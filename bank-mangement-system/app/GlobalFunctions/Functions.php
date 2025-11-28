<?php
use Illuminate\Support\Str;

function getCompleteDetail($value, $isDaybook = NULL)
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
            $memberName = !empty($value->memberCompany) ? $value->memberCompany->member->first_name . ' ' . $value->memberCompany->member->last_name : 'N/A';
            $memberId = !empty($value->memberCompany) ? $value->memberCompany->member->member_id : 'N/A';
            $memberAccount = $memberId;
            $associate_name = !empty($value->associateMember) ? $value->associateMember->first_name . ' ' . $value->associateMember->last_name : 'N/A';
        }
    } elseif ($value->type == 2) {
        if ($value->type_id) {
            $memberName = !empty($value->member_investment->memberCompany) ? $value->member_investment->memberCompany->member->first_name . ' ' . $value->member_investment->memberCompany->member->last_name : 'N/A';
            $memberId = !empty($value->member_investment->memberCompany) ? $value->member_investment->memberCompany->member_id : 'N/A';
            $associate_name = !empty($value->associateMember) ? $value->associateMember->first_name . ' ' . $value->associateMember->last_name : 'N/A';
        }
    } elseif ($value->type == 3) {
        if ($value->member_id) {
            $memberName = !empty($value->member_investment->memberCompany) ? $value->member_investment->memberCompany->member->first_name . ' ' . $value->member_investment->memberCompany->member->last_name : 'N/A';
            $memberId = !empty($value->member_investment->memberCompany) ? $value->member_investment->memberCompany->member_id : 'N/A';
            $plan_name = !empty($value->member_investment->plan) ? $value->member_investment->plan->name : 'N/A';
            $associate_name = !empty($value->associateMember) ? $value->associateMember->first_name . ' ' . $value->associateMember->last_nam : 'N/A';
            $associateno = !empty($value->associateMember) ? $value->associateMember->associate_no : '';
        }
        if ($memberData) {
            $memberAccount = $memberData->account_number;
        }
    } elseif ($value->type == 4) {
        $plan_name = 'Saving Account';
        if ($value->sub_type == 45) {
            $memberId = !empty(getMemberData($value->member_id)) ? getMemberData($value->member_id)->associate_no : '';
            $memberName = !empty(getMemberData($value->member_id)) ? getMemberData($value->member_id)->first_name . ' ' . getMemberData($value->member_id)->last_name : 'N/A';
        } else {
            if (isset($value->memberCompanybyMemberId->member)) {
                $memberName = $value->memberCompanybyMemberId ? $value->memberCompanybyMemberId->member->first_name . ' ' . $value->memberCompanybyMemberId->member->last_name ?? '' : '';
                $memberId = $value->memberCompanybyMemberId ? $value->memberCompanybyMemberId->member->member_id : 'N/A';
            }
        }
        if ($value->associate_id) {
            $associate_name = $value->associateMember ? $value->associateMember->first_name . ' ' . $value->associateMember->last_name ?? '' : 'N/A';
            $associateno = $value->associateMember ? $value->associateMember->associate_no : 'N/A';
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
        if ($value->sub_type == 41) {
            $memberAccount = getSsbAccountNumber($value->type_id, $value->company_id);
            $plan_name = $memberAccount ? getSsbPlanName('S', $memberAccount->company_id) : '';
        } else {
            $memberAccount = getSsbAccountNumber($value->type_id);
        }
        if ($value->sub_type == 43) {
            $memberAccount = getSsbAccountNumber($value->type_id, $value->company_id);
            $plan_name = getSsbPlanName('S', $memberAccount->company_id);
        } else {
            $memberAccount = getSsbAccountNumber($value->type_id);
        }
        if ($memberAccount) {
            //  $plan_name = getSsbPlanName($memberAccount->company_id,'S');
            $memberAccount = $memberAccount->account_no;
        }
        // dd($memberAccount);
    } elseif ($value->type == 5) {
        if (in_array($value->sub_type, [51, 52, 53, 57, 511, 513, 515, 523, 525, 527, 528, 529, 530, 531, 532, 533, 534, 535])) {
            if ($loanData) {
                $memberName = $loanData->member->first_name . ' ' . $loanData->member->last_name;
                $memberId = $loanData->loanMemberCompany->member_id;
                $plan_name = $loanData->loan->name;
                $memberAccount = $loanData->account_number;
                $associate_name = (isset($value->associate_id)) ? (customGetMemberData($value->associate_id)->first_name . ' ' . customGetMemberData($value->associate_id)->last_name ?? '') : '';
            }
        } elseif (in_array($value->sub_type, [54, 55, 56, 58, 512, 514, 516, 518, 524, 526, 526, 536, 537, 538, 539, 540, 541, 542, 543, 544])) {
            if ($groupLoanData) {
                $memberName = $groupLoanData->member->first_name . ' ' . $groupLoanData->member->last_name;
                $memberId = $groupLoanData->loanMemberCompany ? $groupLoanData->loanMemberCompany->member_id : '';
                $plan_name = $groupLoanData->loan->name;
                $memberAccount = $groupLoanData->account_number;
                $associate_name = (isset($value->associate_id)) ? (customGetMemberData($value->associate_id)->first_name . ' ' . customGetMemberData($value->associate_id)->last_name ?? '') : '';
            }
        } elseif (in_array($value->sub_type, [551, 552, 553, 554])) {
            $v = $loanData ?? $groupLoanData;
            $memberName = $v ? $v->member ? $v->member->first_name . ' ' . $v->member->last_name ?? '' : '': '';
            $memberId = $v ? ($v->loanMemberCompany ? $v->loanMemberCompany->member_id : '') : '';
            $memberAccount = $v ? $v->account_number : '';
            $associate_name = $v ? (isset($value->associate_id)) ? $value->associateMember->first_name . " " . $value->associateMember->last_name : "" : '';
            $associateno = $v ?  $value->associateMember ? $value->associateMember->associate_no ?? '' : '' : '';
            $plan_name = $v ? ((($loanData->loan) ? $loanData->loan->name : '' ) ?? (($groupLoanData->loan) ? $groupLoanData->loan->name : '')) : '';        } else {
            // $type = $value->description;
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
        $associate_id = getMemberData($value->member_id) ? getMemberData($value->member_id)->associate_no : 0;
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
            $plan_name = getPlanDetail($plan_id->plan_id, $value->company_id) ? getPlanDetail($plan_id->plan_id, $value->company_id)->name : 'N/A';
            $memberName = !empty(getMemberCustom($plan_id->customer_id ?? $plan_id->member_id)) ? getMemberCustom($plan_id->customer_id ?? $plan_id->member_id)->first_name . ' ' . getMemberCustom($plan_id->customer_id ?? $plan_id->member_id)->last_name : 'N/A';
            $memberId = !empty(getMemberCustom($plan_id->customer_id ?? $plan_id->member_id)) ? getMemberCustom($plan_id->customer_id ?? $plan_id->member_id)->member_id : '';
        }
        if ($value->sub_type == 134) {
            $plan_id = $DemandAdviceData ? getMemberInvestment($DemandAdviceData->investment_id) : '';
            $memberAccount = $plan_id ? $plan_id->account_number : '';
            $plan_name = $plan_id ? getPlanDetail($plan_id->plan_id, $value->company_id) ? getPlanDetail($plan_id->plan_id, $value->company_id)->name : 'N/A' : 'N/A';
            $memberName = $plan_id ? !empty(getMemberCustom($plan_id->customer_id ?? $plan_id->member_id)) ? getMemberCustom($plan_id->customer_id ?? $plan_id->member_id)->first_name . ' ' . getMemberCustom($plan_id->customer_id ?? $plan_id->member_id)->last_name : 'N/A' : '';
            $memberId = $plan_id ? !empty(getMemberCustom($plan_id->customer_id ?? $plan_id->member_id)) ? getMemberCustom($plan_id->customer_id ?? $plan_id->member_id)->member_id : '' : '';
        }
        if ($value->sub_type == 135) {
            $memberAccount = getMemberInvestment($DemandAdviceData->investment_id)->account_number;
            $plan_id = getMemberInvestment($DemandAdviceData->investment_id);
            $plan_name = getPlanDetail($plan_id->plan_id, $value->company_id) ? getPlanDetail($plan_id->plan_id, $value->company_id)->name : 'N/A';
            $memberName = getMemberCustom($plan_id->customer_id ?? $plan_id->member_id)->first_name . ' ' . getMemberCustom($plan_id->customer_id ?? $plan_id->member_id)->last_name;
            $memberId = getMemberCustom($plan_id->customer_id ?? $plan_id->member_id)->member_id;
        }
        if ($value->sub_type == 136) {
            $memberAccount = getMemberInvestment($DemandAdviceData->investment_id)->account_number;
            $plan_id = getMemberInvestment($DemandAdviceData->investment_id);
            $plan_name = getPlanDetail($plan_id->plan_id, $value->company_id) ? getPlanDetail($plan_id->plan_id, $value->company_id)->name : 'N/A';
            $memberName = getMemberCustom($plan_id->customer_id ?? $plan_id->member_id)->first_name . ' ' . getMemberCustom($plan_id->customer_id ?? $plan_id->member_id)->last_name;
            $memberId = getMemberCustom($plan_id->customer_id ?? $plan_id->member_id)->member_id;
        }
        if ($value->sub_type == 137) {
            $data = getMemberInvestment($DemandAdviceData->investment_id);
            // dd($DemandAdviceData->investment_id,$data);
            if (isset($data->account_number)) {
                $memberAccount = $data->account_number;
                $memberName = getMemberCustom($data->customer_id ?? $data->member_id)->first_name . ' ' . getMemberCustom($data->customer_id ?? $data->member_id)->last_name;
                $memberId = getMemberCustom($data->customer_id ?? $data->member_id)->member_id;
            }
            $plan_id = getMemberInvestment($DemandAdviceData->investment_id);
            $plan_name = getPlanDetail($plan_id->plan_id, $value->company_id) ? getPlanDetail($plan_id->plan_id, $value->company_id)->name : 'N/A';
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
        $memberName = getMemberCustom($value->member_id)->first_name . ' ' . getMemberCustom($value->member_id)->last_name;
        $memberId = getMemberCustom($value->member_id)->member_id;
        $memberAccount = getMemberCustom($value->member_id)->member_id;
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
            $rent_type = $record->description ?? '';
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
    // if($type == 'N/A'){
    //     dd($value->type,$value->sub_type);
    // }
    return ['memberName' => $memberName, 'memberAccount' => $memberAccount, 'type' => $type, 'plan_name' => $plan_name, 'rent_type' => $rent_type, 'memberId' => $memberId, 'associate_name' => $associate_name, 'branchName' => $branchName, 'associateno' => $associateno, 'tag' => $tags];
}
function getCompleteDetail2($value, $isDaybook = NULL)
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
            // $memberName = customGetMemberData($value->type_id)->first_name . ' ' . customGetMemberData($value->type_id)->last_name;
            // $memberId = customGetMemberData($value->type_id)->member_id;
            // $memberAccount = customGetMemberData($value->type_id)->member_id;
            $memberName = !empty($value->memberCompany) ? $value->memberCompany->member->first_name . ' ' . $value->memberCompany->member->last_name : 'N/A';
            $memberId = !empty($value->memberCompany) ? $value->memberCompany->member->member_id : 'N/A';
            $memberAccount = $memberId;
            $associate_name = !empty($value->associateMember) ? $value->associateMember->first_name . ' ' . $value->associateMember->last_name : 'N/A';
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
            $associate_name = !empty($value->associateMember) ? $value->associateMember->first_name . ' ' . $value->associateMember->last_nam ?? '' : 'N/A';
            $associateno = !empty($value->associateMember) ? $value->associateMember->associate_no : '';
            //     $memberName = customGetMemberData($value->member_id)->first_name . ' ' . customGetMemberData($value->member_id)->last_name;
            //     $plan_name = getPlanDetail($memberData->plan_id)->name;
            //     $memberId = customGetMemberData($value->member_id)->member_id;
        }
        if ($memberData) {
            $memberAccount = $memberData->account_number;
        }
    } elseif ($value->type == 4) {
        $plan_name = 'Saving Account';
        if ($value->sub_type == 45) {
            $memberId = !empty(customGetMemberData($value->member_id)) ? customGetMemberData($value->member_id)->associate_no : '';
            $memberName = !empty(customGetMemberData($value->member_id)) ? customGetMemberData($value->member_id)->first_name . ' ' . customGetMemberData($value->member_id)->last_name : 'N/A';
        } else {
            if ($value->member_id) {
                $memberName = $value->memberCompanybyMemberId ? $value->memberCompanybyMemberId->member->first_name . ' ' . $value->memberCompanybyMemberId->member->last_name ?? '' : '';
                $memberId = $value->memberCompanybyMemberId ? $value->memberCompanybyMemberId->member->member_id : 'N/A';
            }
        }
        if ($value->associate_id) {
            $associate_name = $value->associateMember->first_name . ' ' . $value->associateMember->last_name;
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
                $memberName = $loanData->member->first_name . ' ' . $loanData->member->last_name;
                $memberId = $loanData->loanMemberCompany->member_id;
                $plan_name = $loanData->loan->name;
                $memberAccount = $loanData->account_number;
                $associate_name = (isset($value->associate_id)) ? $value->associateMember->first_name . " " . $value->associateMember->last_name : "";
            }
        } elseif ($value->sub_type == 54 || $value->sub_type == 55 || $value->sub_type == 56 || $value->sub_type == 58 || $value->sub_type == 512 || $value->sub_type == 514 || $value->sub_type == 516 || $value->sub_type == 518 || $value->sub_type == 524 || $value->sub_type == 526 || $value->sub_type == 526 || $value->sub_type == 536 || $value->sub_type == 537 || $value->sub_type == 538 || $value->sub_type == 539 || $value->sub_type == 540 || $value->sub_type == 541 || $value->sub_type == 542 || $value->sub_type == 543 || $value->sub_type == 544) {
            if ($groupLoanData) {
                $memberName = $groupLoanData->member->first_name . ' ' . $groupLoanData->member->last_name;
                $memberId = $groupLoanData->loanMemberCompany->member_id;
                $plan_name = $groupLoanData->loan->name;
                $memberAccount = $groupLoanData->account_number;
                $associate_name = (isset($value->associate_id)) ? $value->associateMember->first_name . " " . $value->associateMember->last_name : "";
            }
        } elseif (in_array($value->sub_type, [551, 552, 553, 554])) {
            $v = $loanData ?? $groupLoanData;
            $memberName = $v->member ? $v->member->first_name . ' ' . $v->member->last_name ?? '' : '';
            $memberId = $v->loanMemberCompany ? $v->loanMemberCompany->member_id : '';
            $memberAccount = $v->account_number;
            $associate_name = (isset($value->associate_id)) ? $value->associateMember->first_name . " " . $value->associateMember->last_name : "";
            $associateno = $value->associateMember ? $value->associateMember->associate_no ?? '' : '';
            $plan_name = $v ? $loanData->loan->name : $groupLoanData->loan->name;
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
        $associate_id = customGetMemberData($value->member_id)->associate_no;
        $associate_name = 'N/A';
        if (isset($value->member_id)) {
            $memberName = customGetMemberData($value->member_id)->first_name . ' ' . customGetMemberData($value->member_id)->last_name;
            $memberId = customGetMemberData($value->member_id)->associate_no;
            $memberAccount = customGetMemberData($value->member_id)->associate_no;
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
            $memberAccount = getMemberInvestment($DemandAdviceData->investment_id)->account_number;
            $plan_id = getMemberInvestment($DemandAdviceData->investment_id);
            $plan_name = getPlanDetail($plan_id->plan_id, $value->company_id) ? getPlanDetail($plan_id->plan_id, $value->company_id)->name : 'N/A';
            $memberName = !empty(customGetMemberData($plan_id->member_id)) ? customGetMemberData($plan_id->member_id)->first_name . ' ' . customGetMemberData($plan_id->member_id)->last_name : 'N/A';
            $memberId = !empty(customGetMemberData($plan_id->member_id)) ? customGetMemberData($plan_id->member_id)->member_id : '';
        }
        if ($value->sub_type == 134) {
            $memberAccount = getMemberInvestment($DemandAdviceData->investment_id)->account_number;
            $plan_id = getMemberInvestment($DemandAdviceData->investment_id);
            $plan_name = getPlanDetail($plan_id->plan_id, $value->company_id) ? getPlanDetail($plan_id->plan_id, $value->company_id)->name : 'N/A';
            $memberName = !empty(customGetMemberData($plan_id->member_id)) ? customGetMemberData($plan_id->member_id)->first_name . ' ' . customGetMemberData($plan_id->member_id)->last_name : 'N/A';
            $memberId = !empty(customGetMemberData($plan_id->member_id)) ? customGetMemberData($plan_id->member_id)->member_id : '';
        }
        if ($value->sub_type == 135) {
            $memberAccount = getMemberInvestment($DemandAdviceData->investment_id)->account_number;
            $plan_id = getMemberInvestment($DemandAdviceData->investment_id);
            $plan_name = getPlanDetail($plan_id->plan_id, $value->company_id) ? getPlanDetail($plan_id->plan_id, $value->company_id)->name : 'N/A';
            $memberName = customGetMemberData($plan_id->member_id)->first_name . ' ' . customGetMemberData($plan_id->member_id)->last_name;
            $memberId = customGetMemberData($plan_id->member_id)->member_id;
        }
        if ($value->sub_type == 136) {
            $memberAccount = getMemberInvestment($DemandAdviceData->investment_id)->account_number;
            $plan_id = getMemberInvestment($DemandAdviceData->investment_id);
            $plan_name = getPlanDetail($plan_id->plan_id, $value->company_id) ? getPlanDetail($plan_id->plan_id, $value->company_id)->name : 'N/A';
            $memberName = customGetMemberData($plan_id->member_id)->first_name . ' ' . customGetMemberData($plan_id->member_id)->last_name;
            $memberId = customGetMemberData($plan_id->member_id)->member_id;
        }
        if ($value->sub_type == 137) {
            $data = getMemberInvestment($DemandAdviceData->investment_id);
            if (isset($data->account_number)) {
                $memberAccount = $data->account_number;
                $memberName = customGetMemberData($data->member_id)->first_name . ' ' . customGetMemberData($data->member_id)->last_name;
                $memberId = customGetMemberData($data->member_id)->member_id;
            }
            $plan_id = getMemberInvestment($DemandAdviceData->investment_id);
            $plan_name = getPlanDetail($plan_id->plan_id, $value->company_id) ? getPlanDetail($plan_id->plan_id, $value->company_id)->name : 'N/A';
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
        $memberName = customGetMemberData($value->member_id)->first_name . ' ' . customGetMemberData($value->member_id)->last_name;
        $memberId = customGetMemberData($value->member_id)->member_id;
        $memberAccount = customGetMemberData($value->member_id)->member_id;
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
function tdsCalculate($currentInterst, $investmentDetails, $demandDetails, $type = NULL, $fstrtdate, $fenddate)
{
    $demandDate = (isset($demandDetails->date)) ? $demandDetails->date : $demandDetails;
    $fstrtdate = date('Y-m-d', strtotime($fstrtdate));
    $fenddate = date('Y-m-d', strtotime($fenddate));
    $memberId = isset($investmentDetails->member_id) ? $investmentDetails->member_id : $investmentDetails->employee_id;
    $memberType = ($type != 'rent') ? 0 : 1;
    $formG = App\Models\Form15G::where('member_id', $memberId)
        ->where('type', $memberType)
        ->where(function ($q) use ($fstrtdate, $fenddate) {
            $q->where('year', $fstrtdate)->orwhere('max_year', $fenddate);
        })
        ->whereNotNull('file')
        ->whereStatus('1')
        ->where('is_deleted', '0')
        ->first();
    $getBranch = getBranchDetail($investmentDetails->branch_id);
    $state_id = $getBranch->state_id;
    $globaldate = checkMonthAvailability(date('d'), date('m'), date('Y'), $state_id);
    $globaldate = date('Y-m-d', strtotime($globaldate));
    $transType = ($type != 'rent') ? 1 : 3;
    $checkTdsExist = App\Models\TdsDeductionSetting::where('type', $transType)->where('beneficiary_type', 1)->where('effective_from_date', '<=', $globaldate)->whereNull('effective_to_date')->where('minlimit', '<=', $currentInterst)->where('maxlimit', '>=', $currentInterst)->orderBy('created_at', 'desc')->first();
    $investmentTds = ($type != 'rent') ? App\Models\MemberInvestmentInterestTds::where('investment_id', $investmentDetails->id)->sum('tdsamount_on_interest') : 0;
    if ($checkTdsExist) {
        if ($currentInterst >= $checkTdsExist->minlimit && $currentInterst <= $checkTdsExist->maxlimit) {
            $memberData = $investmentDetails->member->dob;
            $diff = abs(strtotime($demandDate) - strtotime($memberData)) ?? 0;
            $years = floor($diff / (365 * 60 * 60 * 24));
            $penCard = get_member_id_proof($investmentDetails->member_id, 5);
            $tdsDetail = $tdsPercentage = ($penCard) ? $checkTdsExist->tds_pan : $checkTdsExist->tds_no_pan;
            if ($years >= '60' && $years < '80') {
                // $tdsAmountonInterest  = $investmentTds = $tdsAmount  = ($currentInterst >=  $checkTdsExist->h_limit)  ?  $tdsDetail * $currentInterst / 100 : 0;
                if ($formG) {
                    $tdsAmountonInterest = $investmentTds = $tdsAmount = ($currentInterst >= $checkTdsExist->h_limit)
                        ? (($type != 'rent') ? $tdsDetail * $currentInterst / 100 : $tdsDetail * $investmentDetails->rent / 100)
                        :
                        0;
                } else {
                    // $tdsAmountonInterest = $investmentTds = $tdsAmount = 0;
                    $tdsAmountonInterest = $investmentTds = $tdsAmount = ($type != 'rent') ? $tdsDetail * $currentInterst / 100 : $tdsDetail * $investmentDetails->rent / 100;
                    $tdsDetail * $currentInterst / 100;
                }
            }
            if ($years >= '80') {
                if ($formG) {
                    $tdsAmountonInterest = $investmentTds = $tdsAmount = ($currentInterst >= $checkTdsExist->h_super_senior_limit)
                        ? (($type != 'rent') ? $tdsDetail * $currentInterst / 100 : $tdsDetail * $investmentDetails->rent / 100)
                        :
                        0;
                } else {
                    // $tdsAmountonInterest = $investmentTds = $tdsAmount = 0;
                    $tdsAmountonInterest = $investmentTds = $tdsAmount = ($type != 'rent') ? $tdsDetail * $currentInterst / 100 : $tdsDetail * $investmentDetails->rent / 100;
                    $tdsDetail * $currentInterst / 100;
                }
                // dd($checkTdsExist);
            }
            if ($years < '60') {
                if ($formG) {
                    if ($currentInterst >= $checkTdsExist->g_limit) {
                        $tdsAmountonInterest = $investmentTds = $tdsAmount = ($type != 'rent') ? $tdsDetail * $currentInterst / 100 : $tdsDetail * $investmentDetails->rent / 100;
                    } else {
                        $tdsAmountonInterest = $investmentTds = $tdsAmount = 0;
                    }
                } else {
                    $tdsAmountonInterest = $investmentTds = $tdsAmount = ($type != 'rent') ? $tdsDetail * $currentInterst / 100 : $tdsDetail * $investmentDetails->rent / 100;
                    $tdsDetail * $currentInterst / 100;
                }
            }
            $tdsAmountonInterest = round($tdsAmountonInterest);
            $investmentTds = $investmentTds + $tdsAmountonInterest;
            return ['tdsAmount' => $tdsAmountonInterest, 'tdsDetail' => $tdsDetail, 'tdsPercentage' => $tdsPercentage, 'tdsApplicable' => 0];
        } else {
            return ['tdsAmount' => 0, 'tdsDetail' => 0, 'tdsPercentage' => 0, 'tdsApplicable' => 1];
        }
    } else {
        return ['tdsAmount' => 0, 'tdsDetail' => 0, 'tdsPercentage' => 0, 'tdsApplicable' => 1];
    }
}
// function maturityCalculation($mInvestment,$type=NULL,$investmentMonths,$ActualInterest)
// {
//     $totalInterest = 0;
//     $currentAmount = 0;
//     $currentInterst = 0;
//     $regularInterest = 0;
//     $totalInterestDeposit = 0;
//     $iss = 0;
//     $defaulter = 0;
//     $dataArray = array();
//     $total = 0;
//     $defaulterInterest = 0;
//     $investmentMonths = ($mInvestment->plan_id == 10) ? $investmentMonths + 1 :  $investmentMonths;
//     $totalInvestmentAmount = App\Models\Daybook::select('investment_id', 'deposit', 'created_at')->where('investment_id', $mInvestment->id)->whereDate('created_at', '>=', $mInvestment->created_at)->whereIn('transaction_type', [2, 4])->sum('deposit');
//     $val = $mInvestment;
//     $createdDate  =date('Y-m-d', strtotime($mInvestment->created_at));
//     for ($i = 1; $i <= $investmentMonths; $i++) {
//         $v = $i - 1;
//         $currentInterst = $ActualInterest;
//         $investmentmonth = date('m', strtotime($mInvestment->created_at));
//         $investmentyear = date('Y', strtotime($mInvestment->created_at));
//         $tdate = $investmentyear . '-' . $investmentmonth . '-01';
//         $nDate = date('Y-m-d', strtotime($tdate . ' + ' . $v . ' months'));
//         $newdate = date("Y-m-d", strtotime("" . $v . " month", strtotime($tdate)));
//         if ($newdate != date("Y-m-t", strtotime($newdate))) {
//             $newdate = date("Y-m-t", strtotime($newdate));
//         }
//         $dMonth = date("Y-m-d", strtotime(convertDate($newdate)));
//         $nDate = date('Y-m-d', strtotime($tdate . ' + ' . $i . ' months'));
//         $nMonth = date('m', strtotime($dMonth));
//         $nYear = date('Y', strtotime($dMonth));
//         $lastdate = \Carbon\Carbon::parse($createdDate)->addMonthNoOverflow()->subDay();
//         $ts1 = strtotime($mInvestment->created_at);
//         $ts2 = strtotime($nDate);
//         $year1 = date('Y', $ts1);
//         $year2 = date('Y', $ts2);
//         $month1 = date('m', $ts1);
//         $month2 = date('m', $ts2);
//         $monthDiff = (($year2 - $year1) * 12) + ($month2 - $month1);
//         $totalDeposit = $totalInvestmentAmount;
//         $depositeAmount = \App\Models\Daybook::select('deposit')->whereIn('transaction_type', [2, 4])->where('account_no', $mInvestment->account_number)->where(\DB::raw('date(created_at)'),'>=', $createdDate)->where(\DB::raw('date(created_at)'),'<=', $lastdate)->sum('deposit');
//         $totalDeposite = ( $mInvestment->plan_id == 7 ) ? 25 *  $i * $mInvestment->deposite_amount : $i * $mInvestment->deposite_amount;
//         $total = $total + $depositeAmount;
//         $defaultInterest = ($total < $totalDeposite) ? 1.5 : 0;
//         $defaulter =  ($total < $totalDeposite) ? 1 : 0;
//         $currentInterst = $currentInterst - $defaultInterest;
//         if ($mInvestment->deposite_amount * $monthDiff <= $totalDeposit) {
//             $aviAmount = $depositeAmount;
//             if ($monthDiff % 3 == 0 && $monthDiff != 0) {
//                 $currentAmount = $currentAmount + $depositeAmount;
//                 $interest = (($currentAmount) * $currentInterst) / 1200;
//                 $totalInterest = $interest;
//                 $currentAmount = $regularInterest + $interest + $currentAmount;
//                 $iss = $regularInterest + $interest;
//                 $totalInterestDeposit = $totalInterestDeposit + $interest;
//                 if (($monthDiff % $investmentMonths) == 0) {
//                     $regularInterest = $regularInterest + $interest;
//                     $currentAmount = $currentAmount - $regularInterest;
//                 } else {
//                     $regularInterest = 0;
//                 }
//             } else {
//                 $iss = 0;
//                 $currentAmount = $currentAmount + $depositeAmount;
//                 $interest = (($currentAmount) * $currentInterst) / 1200;
//                 $totalInterest = $interest;
//                 $regularInterest = $regularInterest + $interest;
//                 $totalInterestDeposit = $totalInterestDeposit + $interest;
//             }
//         } elseif ($mInvestment->deposite_amount * $monthDiff > $totalDeposit) {
//             $aviAmount = $depositeAmount;
//             // $total = $depositeAmount;
//             if ($monthDiff % 3 == 0 && $monthDiff != 0) {
//                 $currentAmount = $currentAmount + $depositeAmount;
//                 $interest = (($currentAmount) * $currentInterst) / 1200;
//                 $totalInterest = $interest;
//                 $currentAmount = $regularInterest + $interest + $currentAmount;
//                 $totalInterestDeposit = $totalInterestDeposit + $interest;
//                 $iss = $regularInterest + $interest;
//                 if (($monthDiff % $investmentMonths) == 0) {
//                     $regularInterest = $regularInterest + $interest;
//                     $currentAmount = $currentAmount - $regularInterest;
//                 } else {
//                     $regularInterest = 0;
//                 }
//             } else {
//                 $iss = 0;
//                 $currentAmount = $currentAmount + $depositeAmount;
//                 $interest = (($currentAmount) * $currentInterst) / 1200;
//                 $totalInterest = $interest;
//                 $regularInterest = $regularInterest + $interest;
//                 $totalInterestDeposit = $totalInterestDeposit + $interest;
//             }
//         }
//         $dataArray[] = [
//             'investment_id' => $mInvestment->id,
//             'deposite' => $aviAmount,
//             'compound_interest' => $iss,
//             'total' => $total,
//             'interest_rate' => $currentInterst,
//             'interest_rate_amount' => $totalInterest,
//             'deposite_date' => date("Y-m-d", strtotime(convertDate($newdate))),
//             'total_amount' => $totalInvestmentAmount,
//             'tds_amount' => 0,
//             'final_amount' => $totalInvestmentAmount + $totalInterestDeposit
//         ];
//         $createdDate = \Carbon\Carbon::parse($lastdate)->addDay();
//     }
//     $recordData = array();
//     if($type != 'demand_create')
//     {
//         \App\Models\MaturityCalculate::where('investment_id', $mInvestment->id)->delete();
//         $recordData = \App\Models\MaturityCalculate::insert($dataArray);
//     }
//     $dataInsert = ['data' => $recordData,'defaulter' =>$defaulter,'final_amount'=>  $totalInvestmentAmount + $totalInterestDeposit];
//     return $dataInsert;
// }
function maturityCalculation($mInvestment, $type = NULL, $investmentMonths, $ActualInterest)
{
    $totalInterest = 0;
    $currentAmount = 0;
    $currentInterst = 0;
    $regularInterest = 0;
    $totalInterestDeposit = 0;
    $iss = 0;
    $defaulter = 0;
    $dataArray = array();
    $total = 0;
    $defaulterInterest = 0;
    /**on 8 january 2024 discussed with alpana mam and anoop sir we are removing this on call*/
    // $investmentMonths = ($mInvestment->plan_id == 10) ? $investmentMonths + 1 : $investmentMonths;
    $totalInvestmentAmount = App\Models\Daybook::select('investment_id', 'deposit', 'created_at')->where('investment_id', $mInvestment->id)->where('account_no', $mInvestment->account_number)->whereDate('created_at', '>=', $mInvestment->created_at)->whereIn('transaction_type', [2, 4])->where('is_deleted', 0)->sum('deposit');
    $totalInvestmentWAmount = App\Models\Daybook::select('investment_id', 'deposit', 'created_at')->where('investment_id', $mInvestment->id)->where('account_no', $mInvestment->account_number)->whereDate('created_at', '>=', $mInvestment->created_at)->whereIn('transaction_type', [18])->where('is_deleted', 0)->sum('withdrawal');
    $val = $mInvestment;
    $createdDate = date('Y-m-d', strtotime($mInvestment->created_at));
    $createdDate2 = date('Y-m-d', strtotime($mInvestment->created_at));
    if ($mInvestment->plan->plan_category_code != 'F') {
        for ($i = 1; $i <= $investmentMonths; $i++) {
            $v = $i - 1;
            $currentInterst = $ActualInterest;
            $investmentmonth = date('m', strtotime($mInvestment->created_at));
            $investmentyear = date('Y', strtotime($mInvestment->created_at));
            $tdate = $investmentyear . '-' . $investmentmonth . '-01';
            $nDate = date('Y-m-d', strtotime($tdate . ' + ' . $v . ' months'));
            $newdate = date("Y-m-d", strtotime("" . $v . " month", strtotime($tdate)));
            if ($newdate != date("Y-m-t", strtotime($newdate))) {
                $newdate = date("Y-m-t", strtotime($newdate));
            }
            $dMonth = date("Y-m-d", strtotime(convertDate($newdate)));
            $nDate = date('Y-m-d', strtotime($tdate . ' + ' . $i . ' months'));
            $nMonth = date('m', strtotime($dMonth));
            $nYear = date('Y', strtotime($dMonth));
            //  $lastdate = \Carbon\Carbon::parse($createdDate)->addMonthNoOverflow()->subDay();
            /**Updated upper code below by mahesh on 10 january */
            $lastdate = \Carbon\Carbon::parse($createdDate2)->addMonthsNoOverflow($i)->subDay();
            $ts1 = strtotime($mInvestment->created_at);
            $ts2 = strtotime($nDate);
            $year1 = date('Y', $ts1);
            $year2 = date('Y', $ts2);
            $month1 = date('m', $ts1);
            $month2 = date('m', $ts2);
            $monthDiff = (($year2 - $year1) * 12) + ($month2 - $month1);
            $totalDeposit = $totalInvestmentAmount - $totalInvestmentWAmount ?? 0;
            $depositeAmount = \App\Models\Daybook::select('deposit')->whereIn('transaction_type', [2, 4])->where('account_no', $mInvestment->account_number)->where(\DB::raw('date(created_at)'), '>=', $createdDate)->where(\DB::raw('date(created_at)'), '<=', $lastdate)->where('is_deleted', 0)->sum('deposit');
            $withdrawalAmount = \App\Models\Daybook::select('withdrawal')->whereIn('transaction_type', [18])->where('account_no', $mInvestment->account_number)->where(\DB::raw('date(created_at)'), '>=', $createdDate)->where(\DB::raw('date(created_at)'), '<=', $lastdate)->where('is_deleted', 0)->sum('withdrawal');
            $totalDeposite = ($mInvestment->plan_id == 7) ? 25 * $i * $mInvestment->deposite_amount : $i * $mInvestment->deposite_amount;
            $depositeAmount = $depositeAmount;
            $total = $total + $depositeAmount;
            if (in_array($mInvestment->plan->plan_category_code, array('D', 'M'))) {
                $defaultInterest = ($total < $totalDeposite) ? 1.5 : 0;
                $defaulter = ($total < $totalDeposite) ? 1 : 0;
                $currentInterst = $currentInterst - $defaultInterest;
            }
            if ($mInvestment->deposite_amount * $monthDiff <= $totalDeposit) {
                $aviAmount = $depositeAmount;
                if ($monthDiff % 3 == 0 && $monthDiff != 0) {
                    $currentAmount = $currentAmount + $depositeAmount - $withdrawalAmount;
                    $interest = (($currentAmount) * $currentInterst) / 1200;
                    $totalInterest = $interest;
                    $currentAmount = $regularInterest + $interest + $currentAmount;
                    $iss = $regularInterest + $interest;
                    $totalInterestDeposit = $totalInterestDeposit + $interest;
                    if (($monthDiff % $investmentMonths) == 0) {
                        $regularInterest = $regularInterest + $interest;
                        $currentAmount = $currentAmount - $regularInterest;
                    } else {
                        $regularInterest = 0;
                    }
                } else {
                    $iss = 0;
                    $currentAmount = $currentAmount + $depositeAmount - $withdrawalAmount;
                    $interest = (($currentAmount) * $currentInterst) / 1200;
                    $totalInterest = $interest;
                    $regularInterest = $regularInterest + $interest;
                    $totalInterestDeposit = $totalInterestDeposit + $interest;
                }
            } elseif ($mInvestment->deposite_amount * $monthDiff > $totalDeposit) {
                $aviAmount = $depositeAmount;
                // $total = $depositeAmount;
                if ($monthDiff % 3 == 0 && $monthDiff != 0) {
                    $currentAmount = $currentAmount + $depositeAmount - $withdrawalAmount;
                    $interest = (($currentAmount) * $currentInterst) / 1200;
                    $totalInterest = $interest;
                    $currentAmount = $regularInterest + $interest + $currentAmount;
                    $totalInterestDeposit = $totalInterestDeposit + $interest;
                    $iss = $regularInterest + $interest;
                    if (($monthDiff % $investmentMonths) == 0) {
                        $regularInterest = $regularInterest + $interest;
                        $currentAmount = $currentAmount - $regularInterest;
                    } else {
                        $regularInterest = 0;
                    }
                } else {
                    $iss = 0;
                    $currentAmount = $currentAmount + $depositeAmount - $withdrawalAmount;
                    $interest = (($currentAmount) * $currentInterst) / 1200;
                    $totalInterest = $interest;
                    $regularInterest = $regularInterest + $interest;
                    $totalInterestDeposit = $totalInterestDeposit + $interest;
                }
            }
            $dataArray[] = [
                'investment_id' => $mInvestment->id,
                'deposite' => $aviAmount,
                'compound_interest' => $iss,
                'total' => $total - $withdrawalAmount,
                'interest_rate' => $currentInterst,
                'interest_rate_amount' => $totalInterest,
                // 'deposite_date' => date("Y-m-d", strtotime(convertDate($newdate))),
                // modified on 11 jan 2024 by mahesh
                'deposite_date' => date("Y-m-d", strtotime(convertDate($lastdate))),
                'total_amount' => $totalInvestmentAmount,
                'tds_amount' => 0,
                'final_amount' => $totalInvestmentAmount + $totalInterestDeposit - $totalInvestmentWAmount ?? 0
            ];
            $createdDate = \Carbon\Carbon::parse($lastdate)->addDay();
        }
    } else {
        $lastdate = \Carbon\Carbon::parse($createdDate)->addMonthNoOverflow()->subDay();
        $defaulterInterest = 0;
        $irate = ($ActualInterest - $defaulterInterest) / 1;
        $year = $investmentMonths / 12;
        $year = (float) $year;
        $rate = pow((1 + $irate / 100), $year);
        $Rate = substr($rate, 0, strpos($rate, '.') + 3);
        $result = ($mInvestment->deposite_amount * (pow((1 + $irate / 100), $year)) - $mInvestment->deposite_amount);
        $dataArray[] = [
            'investment_id' => $mInvestment->id,
            'deposite' => $totalInvestmentAmount,
            'compound_interest' => 0,
            'total' => $totalInvestmentAmount - $totalInvestmentWAmount,
            'interest_rate' => $ActualInterest,
            'interest_rate_amount' => $result,
            'deposite_date' => date("Y-m-d", strtotime(convertDate($createdDate))),
            'total_amount' => $totalInvestmentAmount,
            'tds_amount' => 0,
            'final_amount' => $totalInvestmentAmount + $totalInterestDeposit - $totalInvestmentWAmount ?? 0
        ];
        $createdDate = \Carbon\Carbon::parse($lastdate)->addDay();
    }
    $recordData = array();
    if ($type != 'demand_create') {
        \App\Models\MaturityCalculate::where('investment_id', $mInvestment->id)->delete();
        $recordData = \App\Models\MaturityCalculate::insert($dataArray);
    }
    $dataInsert = ['data' => $recordData, 'defaulter' => $defaulter, 'final_amount' => $totalInvestmentAmount + $totalInterestDeposit - $totalInvestmentWAmount];
    return $dataInsert;
}
function defaulterCalculate($total, $month, $deposit)
{
    $defaultInterest = ($total = ($month * $deposit)) ? 0 : 1.5;
    return $defaultInterest;
}
function generateOtp()
{
    $generator = "135792468";
    $otp = '';
    for ($i = 1; $i <= 4; $i++) {
        $otp .= substr($generator, (rand() % (strlen($generator))), 1);
    }
    return $otp;
}
function branchCompany()
{
    // $branch = $getBranchId->id;
    // $company = \App\Models\Companies::with(['companybranchs'=>function($query){$query->whereBranch_id($branch)->get();}])->pluck('name','id');
    $company = ['1' => 'bdemo name'];
    return $company;
}
function checkBankBalance($request)
{
    $entryDate = date('Y-m-d', strtotime(convertDate($request->entry_date)));
    $getData = \App\Models\BankBalance::whereBankId($request->bank_id)
        ->whereAccountId($request->account_id)
        ->when(isset($request->company_id), function ($q) use ($request) {
            $q->whereCompanyId($request->company_id);
        })
        ->Where('entry_date', '<=', $entryDate)
        ->sum('totalAmount');
    return $getData;
}
// function getRoi ($interestData,$investmentMonths,$mInvestment)
// {
//     $response = array();
//     $roiExist = false;
//     $ActualInterest = 0;
//     $month = '';
//     foreach ($interestData as $key => $iData) {
//         // if ($mInvestment->plan_id == 10) {
//         //     $newTenure = $mInvestment->tenure * 12;
//         //     if ($newTenure == $iData->tenure) {
//         //         $ActualInterest = $mInvestment['member']->special_category_id != 0 ? $iData->spl_roi : $iData->roi;
//         //         $roiExist = true;
//         //     }
//         // } else {
//             if ($investmentMonths >= $iData->tenure ) {
//                 $ActualInterest = $mInvestment['member']->special_category_id != 0 ? $iData->spl_roi : $iData->roi;
//                 $roiExist = true;
//                 $investmentMonths = $iData->tenure;
//             }
//             if($investmentMonths >= $iData->month_from && $investmentMonths <= $iData->month_to)
//             {
//                 $ActualInterest = $mInvestment['member']->special_category_id != 0 ? $iData->spl_roi : $iData->roi;
//                 $roiExist = true;
//             }
//         }
//             $response = [
//                 'roiExist' =>$roiExist,
//                 'ActualInterest' => $ActualInterest
//             ];
//             return  $response;
// }
function getRoi($interestData, $investmentMonths, $mInvestment, $subType = NULL)
{
    $response = array();
    $roiExist = false;
    $ActualInterest = 0;
    $month = '';
    $openingDate = \Carbon\Carbon::parse($mInvestment->created_at);
    $openingDate = \Carbon\Carbon::parse($openingDate->format('Y-m-d'));
    foreach ($interestData as $key => $iData) {
        if ($subType == 5) {
            $effectiveFromDate = \Carbon\Carbon::parse($iData->effective_from)->format('Y-m-d');
            $effectiveToDate = \Carbon\Carbon::parse($iData->effective_to)->format('Y-m-d');
            if ($mInvestment->plan->plan_sub_category_code == 'X') {
                if ($investmentMonths >= 6 && $investmentMonths < 12) {
                    $elapsedMonth = 12;
                } else {
                    $elapsedMonth = $investmentMonths;
                }
                if (round($mInvestment->tenure * 12) == $iData->tenure && $openingDate->between($effectiveFromDate, $effectiveToDate) && $elapsedMonth >= $iData->month_from && $elapsedMonth < $iData->month_to) {
                    $ActualInterest = $mInvestment['member']->special_category_id != 0 ? $iData->spl_roi : $iData->roi;
                    $roiExist = true;
                    $investmentMonths = $iData->tenure;
                }
            } else {
                if ($investmentMonths >= 6) {
                    if (($mInvestment->tenure * 12) == $iData->tenure && $openingDate->between($effectiveFromDate, $effectiveToDate)) {
                        $ActualInterest = $mInvestment['member']->special_category_id != 0 ? $iData->spl_roi : $iData->roi;
                        $roiExist = true;
                        $investmentMonths = $iData->tenure;
                    }
                } else {
                    $ActualInterest = 0;
                    $roiExist = true;
                }
            }
        } else {
            $effectiveFromDate = \Carbon\Carbon::parse($iData->effective_from)->format('Y-m-d');
            $effectiveToDate = \Carbon\Carbon::parse($iData->effective_to)->format('Y-m-d');
            if ($investmentMonths >= $iData->tenure && $openingDate->between($effectiveFromDate, $effectiveToDate)) {
                $ActualInterest = $mInvestment['member']->special_category_id != 0 ? $iData->spl_roi : $iData->roi;
                $roiExist = true;
                $investmentMonths = $iData->tenure;
            }
            if ($investmentMonths >= $iData->month_from && $investmentMonths <= $iData->month_to && $openingDate->between($effectiveFromDate, $effectiveToDate)) {
                $ActualInterest = $mInvestment['member']->special_category_id != 0 ? $iData->spl_roi : $iData->roi;
                $roiExist = true;
            }
        }
    }
    $response = [
        'roiExist' => $roiExist,
        'ActualInterest' => $ActualInterest
    ];
    return $response;
}
function checkGstData($companyId, $stateid, $applicationDate)
{
    $stateid = Auth::user()->branch->state_id;
    $getHeadSetting = \App\Models\HeadSetting::where('head_id', 294)->first();
    $getHeadSettingFileCHrage = \App\Models\HeadSetting::where('head_id', 90)->first();
    $getHeadSettingEcscharge = \App\Models\HeadSetting::where('head_id', 434)->first();
    $getGstSetting = \App\Models\GstSetting::whereStateId($stateid)->where('applicable_date', '<=', $applicationDate)->whereCompanyId($companyId)->exists();
    $getGstSettingno = \App\Models\GstSetting::select('id', 'gst_no', 'state_id')->whereStateId($stateid)->where('applicable_date', '<=', $applicationDate)->whereCompanyId($companyId)->first();
    $gstData = array();
    $gstFileChargeData = array();
    $gstEcsChargeData = array();
    //Gst Insuramce
    if (isset($getHeadSetting->gst_percentage) && $getGstSetting) {
        $gstData['gst_percentage'] = $getHeadSetting->gst_percentage;
        $gstData['IntraState'] = ($stateid == $getGstSettingno->state_id ? true : false);
    } else {
        $gstData['gst_percentage'] = '0';
        $gstData['IntraState'] = false;
    }
    if (isset($getHeadSettingFileCHrage->gst_percentage) && $getGstSetting) {
        $gstFileChargeData['gst_percentage'] = $getHeadSettingFileCHrage->gst_percentage;
        $gstFileChargeData['IntraState'] = (($stateid == $getGstSettingno->state_id) ? true : false);
    } else {
        $gstFileChargeData['gst_percentage'] = '0';
        $gstFileChargeData['IntraState'] = false;
    }
    if (isset($getHeadSettingEcscharge->gst_percentage) && $getGstSetting) {
        $gstEcsChargeData['gst_percentage'] = $getHeadSettingEcscharge->gst_percentage;
        $gstEcsChargeData['IntraState'] = (($stateid == $getGstSettingno->state_id) ? true : false);
    } else {
        $gstEcsChargeData['gst_percentage'] = '0';
        $gstEcsChargeData['IntraState'] = false;
    }


    return (['gstData' => $gstData, 'gstFileChargeData' => $gstFileChargeData, 'gstEcsChargeData' => $gstEcsChargeData]);
}

function daybookToDemandAdvice($investment_id)
{
    $data = \App\Models\Daybook::where('investment_id', $investment_id)
        ->whereIn('transaction_type', [2, 4])
        ->groupBy('investment_id')
        ->select(['investment_id, transaction_type, id,deposit'])
        // ->pluck('deposit')
        ->sum('deposit')
    ;
    // dd('sdfgsdjh');
    return $data;
}
function branchbalancecrone($id, $permission)
{
    $branch = getBranchDetailManagerId($id); // getting all data of a branch by manager id in branch table
    $branch_id = $branch->id; // get branch id
    $getCompany = \App\Models\CompanyBranch::whereBranchId($branch_id)->pluck('company_id');
    $branchBalance = 0;
    foreach ($getCompany as $k => $v) {
        $branchBalance += getbranchbankbalanceamounthelper($branch_id, $v); // get branch current balance from branch bank balance view by branch id
    }
    $fundtransfer = getfundtransferPandingAmount($branch_id); // get panding amount from branch transaction as per branch id
    $cash_in_hand = (int) $branch->cash_in_hand;
    $authUser = \App\Models\User::findOrFail($branch->manager_id);  // Assuming the authenticated user is an instance of the User model
    $diff_balance = ($branchBalance - $fundtransfer) <= $cash_in_hand; // check difference in branch balance or cash in hand balance
    if ($cash_in_hand == 0) {
        foreach ($permission as $permission) {
            $authUser->givePermissionTo($permission); // give all permission to auth user (branch) if cash in hand amount is zero
        }
    } else {
        if ($diff_balance) {
            foreach ($permission as $permission) {
                $authUser->givePermissionTo($permission); // give all permision to auth user (branch) if cash in hand amount is grater then branch current balance including fund transfer panding amount
            }
        }
        // else{
        //     $permissions = $authUser->permissions;
        //     foreach ($permissions as $permission) {
        //         if(!in_array($permission->name,['Branch To Ho','SSB Withdraw','Branch to Bank Fund Transfer'])){
        //             $authUser->revokePermissionTo($permission); // revoke all Permission from auth user ( branch ) accept 'Branch To Ho','SSB Withdraw','Branch to Bank Fund Transfer' if and only if branch curent amount balance + fund transfer panding amount is grater then branch cash in hand amount limit
        //         }
        //     }
        //     foreach ($permissions as $permission) {
        //         if(in_array($permission->name,['Branch To Ho','SSB Withdraw','Branch to Bank Fund Transfer'])){
        //             $authUser->givePermissionTo($permission); // give only those 'Branch To Ho','SSB Withdraw','Branch to Bank Fund Transfer' Permission to auth user ( branch ) if and only if branch curent amount balance + fund transfer panding amount is grater then branch cash in hand amount limit
        //         }
        //     }
        // }
        /*
        foreach($permission as $permission){
            $authUser->givePermissionTo($permission); // give all permision to auth user (branch) if cash in hand amount is grater then branch current balance including fund transfer panding amount
        }
        */
        // Log::channel('branchLimit')->info('branch ID - '.$branch_id.' , Branch Code - '.$branch->branch_code.' , Branch Name - '.$branch->name. ' , permission count - ' . count($authUser->permissions->pluck('name')). ' ,  branchBalance - '  .$branchBalance.' , cash_in_hand - '.$cash_in_hand.', permissions - ' . $authUser->permissions->pluck('name'));
    }
}
function branchbalanceInableOrDescablecrone($id, $permission)
{
    $branch = getBranchDetailManagerId($id); // getting all data of a branch by manager id in branch table
    $branch_id = $branch->id; // get branch id
    $getCompany = \App\Models\CompanyBranch::whereBranchId($branch_id)->pluck('company_id');
    $branchBalance = 0;
    foreach ($getCompany as $key => $val) {
        $branchBalance += getbranchbankbalanceamounthelper($branch_id, $val); // get branch current balance from branch bank balance view by branch id
    }
    $fundtransfer = getfundtransferPandingAmount($branch_id); // get panding amount from branch transaction as per branch id
    $cash_in_hand = (int) $branch->cash_in_hand;
    $authUser = \App\Models\User::findOrFail($branch->manager_id);  // Assuming the authenticated user is an instance of the User model
    $diff_balance = ($branchBalance - $fundtransfer) <= $cash_in_hand; // check difference in branch balance or cash in hand balance
    if ($cash_in_hand == 0) {
        foreach ($permission as $permission) {
            $authUser->givePermissionTo($permission); // give all permission to auth user (branch) if cash in hand amount is zero
        }
    } else {
        if ($diff_balance) {
            foreach ($permission as $permission) {
                $authUser->givePermissionTo($permission); // give all permision to auth user (branch) if cash in hand amount is grater then branch current balance including fund transfer panding amount
            }
        } else {
            $permissions = $authUser->permissions;
            // foreach ($permissions as $permission) {
            //     if(!in_array($permission->name,['Branch To Ho','SSB Withdraw','Branch to Bank Fund Transfer','SSB Deposit List'])){
            //         $authUser->revokePermissionTo($permission); // revoke all Permission from auth user ( branch ) accept 'Branch To Ho','SSB Withdraw','Branch to Bank Fund Transfer' if and only if branch curent amount balance + fund transfer panding amount is grater then branch cash in hand amount limit
            //     }
            // }
            // foreach ($permissions as $permission) {
            //     if(in_array($permission->name,['Branch To Ho','SSB Withdraw','Branch to Bank Fund Transfer','SSB Deposit List'])){
            //         $authUser->givePermissionTo($permission); // give only those 'Branch To Ho','SSB Withdraw','Branch to Bank Fund Transfer' Permission to auth user ( branch ) if and only if branch curent amount balance + fund transfer panding amount is grater then branch cash in hand amount limit
            //     }
            // }
            /** as per new Update on 09-10-2023 changes are updated by Sourab
             * for now only those below notPermissions variable named permission are
             * removed from branch panel. for rest all permission are given.
             */
            foreach ($permissions as $permission) {
                $authUser->givePermissionTo($permission);
            }
            $notPermissions = [
                'Passbook view',
                'Passbook Cover View',
                'Passbook Cover Print',
                'Passbook Transaction Print',
                'Cover Print And Pay',
                'Register Loan',
                'Renewal Investment'
            ];
            foreach ($notPermissions as $p) {
                $authUser->revokePermissionTo($p);
            }
        }
        // /*
        foreach ($permission as $permission) {
            $authUser->givePermissionTo($permission); // give all permision to auth user (branch) if cash in hand amount is grater then branch current balance including fund transfer panding amount
        }
        // */
        // Log::channel('branchLimit')->info('branch ID - '.$branch_id.' , Branch Code - '.$branch->branch_code.' , Branch Name - '.$branch->name. ' , permission count - ' . count($authUser->permissions->pluck('name')). ' ,  branchBalance - '  .$branchBalance.' , cash_in_hand - '.$cash_in_hand.', permissions - ' . $authUser->permissions->pluck('name'));
    }
}
function calculateCloserAmount($outstandingAmount, $lastEmiDate, $ROI, $stateId)
{
    $globaldate = checkMonthAvailability(date('d'), date('m'), date('Y'), $stateId);
    $globaldate = date('Y-m-d', strtotime($globaldate));
    $systemDate = $globaldate;
    $lastEmiDate = date('Y-m-d', strtotime(convertDate($lastEmiDate)));
    $systemDate = \Carbon\Carbon::create($systemDate);
    $lastEmiDate = \Carbon\Carbon::parse($lastEmiDate);
    $diffDays = $lastEmiDate->diffInDays($systemDate);
    $restInterest = ($outstandingAmount * $ROI * $diffDays) / 36500;
    $closerAmount = $outstandingAmount + $restInterest;
    return round($closerAmount);
}
// emiAmountUotoTodaysDate is use to get the due total amount as per loan and current system date from approve date
// to closing date or current date
function emiAmountUotoTodaysDate($id, $account_number, $approve_date, $stateId, $emi_option, $emi_amount, $closingDate)
{
    $date = checkMonthAvailability(date('d'), date('m'), date('Y'), $stateId);
    $globaldate = date('Y-m-d', strtotime($date));
    $LoanCreatedDate = date('Y-m-d', strtotime($approve_date));
    $LoanCreatedYear = date('Y', strtotime($approve_date));
    $LoanCreatedMonth = date('m', strtotime($approve_date));
    $closingDate = date('Y-m-d', strtotime($closingDate));
    $LoanClosingYear = date('Y', strtotime($closingDate));
    $LoanClosingMonth = date('m', strtotime($closingDate));
    $query = App\Models\LoanDayBooks::where('loan_id', $id)->where('loan_sub_type', 0)->where('is_deleted', 0);
    if (!empty($account_number)) {
        $query->where('account_number', $account_number);
    }
    $recoverdAmount = $query->sum('deposit');
    $CurrentDateYear = date('Y');
    $CurrentDateMonth = date('m');
    $Today = \Carbon\Carbon::parse($globaldate);
    $Close = \Carbon\Carbon::parse($closingDate);
    if ($emi_option == 1) {
        $daysDiffClose = $Close->diffInMonths($LoanCreatedDate);
        $daysDiffToday = $Today->diffInMonths($LoanCreatedDate);
    }
    if ($emi_option == 2) {
        $daysDiffToday = $Today->diffInWeeks($LoanCreatedDate);
        $daysDiffClose = $Close->diffInWeeks($LoanCreatedDate);
    }
    if ($emi_option == 3) {
        $daysDiffToday = $Today->diffInDays($LoanCreatedDate); // as per discussed with anup sir and removed +1 condication from live
        // $daysDiffToday = $Today->diffInDays($LoanCreatedDate) + 1;
        $daysDiffClose = $Close->diffInDays($LoanCreatedDate);
    }
    $daysDiff = ($daysDiffClose > $daysDiffToday) ? $daysDiffToday : $daysDiffClose;
    $tillDateTotalEmiAmount = $daysDiff * $emi_amount;
    $pendingAmount = $tillDateTotalEmiAmount - $recoverdAmount;
    // dd(['id'=>$id,'account_number'=>$account_number,'approve_date'=>$approve_date,'stateId'=>$stateId,'emi_option'=>$emi_option,'emi_amount'=>$emi_amount,'closing_date'=>$closingDate,'daysDiff'=>$daysDiff,'tillDateTotalEmiAmount'=>$tillDateTotalEmiAmount,'pendingAmount'=>$pendingAmount,'recoverdAmount'=>$recoverdAmount,'globaldate'=>$globaldate,'daysDiffClose'=>$daysDiffClose ,'daysDiffToday'=>$daysDiffToday]);
    $data = ($pendingAmount > 0) ? number_format(ceil($pendingAmount), 2, '.', '') : 0;
    return $data;
}
function loan_closing_amount($id, $account_number, $approve_date, $stateId, $emi_option, $emi_amount, $closingDate)
{
    $date = checkMonthAvailability(date('d'), date('m'), date('Y'), $stateId);
    $globaldate = date('Y-m-d', strtotime($date));
    $LoanCreatedDate = date('Y-m-d', strtotime($approve_date));
    $closingDate = date('Y-m-d', strtotime($closingDate));
    $Today = \Carbon\Carbon::parse($globaldate);
    $Close = \Carbon\Carbon::parse($closingDate);
    if ($emi_option == 1) {
        $daysDiffClose = $Close->diffInMonths($LoanCreatedDate);
        ;
        $daysDiffToday = $Today->diffInMonths($LoanCreatedDate);
    }
    if ($emi_option == 2) {
        $daysDiffToday = $Today->diffInWeeks($LoanCreatedDate);
        $daysDiffClose = $Close->diffInWeeks($LoanCreatedDate);
    }
    if ($emi_option == 3) {
        $daysDiffToday = $Today->diffInDays($LoanCreatedDate) + 1;
        $daysDiffClose = $Close->diffInDays($LoanCreatedDate);
    }
    $daysDiff = ($daysDiffClose > $daysDiffToday) ? $daysDiffToday : $daysDiffClose;
    $tillDateTotalEmiAmount = $daysDiff * $emi_amount;
    $pendingAmount = $tillDateTotalEmiAmount;
    // pd(['id'=>$id,'account_number'=>$account_number,'approve_date'=>$approve_date,'stateId'=>$stateId,'emi_option'=>$emi_option,'emi_amount'=>$emi_amount,'closing_date'=>$closingDate,'daysDiff'=>$daysDiff,'tillDateTotalEmiAmount'=>$tillDateTotalEmiAmount,'pendingAmount'=>$pendingAmount,'globaldate'=>$globaldate,'daysDiffClose'=>$daysDiffClose ,'daysDiffToday'=>$daysDiffToday]);
    $data = ($pendingAmount > 0) ? number_format(ceil($pendingAmount), 2, '.', '') : 0;
    return $data;
}
function getAllSaturdayCount($year)
{
    // Set the timezone
    date_default_timezone_set('UTC');

    // Initialize an array to store the week numbers
    $weekNumbers = [];

    // Loop through each month
    for ($month = 1; $month <= 12; $month++) {

        // Get the last day of the month
        $lastDayOfMonth = date('t', strtotime("$year-$month-01"));

        // Calculate the timestamp for the 2nd Saturday
        $secondSaturday = strtotime("second saturday of $year-$month");

        // Check if the 2nd Saturday is still within the same month and not beyond the last day
        if (date('m', $secondSaturday) == $month && date('d', $secondSaturday) <= $lastDayOfMonth) {
            // Calculate the week number for the 2nd Saturday
            $weekNumbers[] = date('W', $secondSaturday);
        }

        // Calculate the timestamp for the 4th Saturday
        $fourthSaturday = strtotime("fourth saturday of $year-$month");

        // Check if the 4th Saturday is still within the same month and not beyond the last day
        if (date('m', $fourthSaturday) == $month && date('d', $fourthSaturday) <= $lastDayOfMonth) {
            // Calculate the week number for the 4th Saturday
            $weekNumbers[] = date('W', $fourthSaturday);
        }
    }

    // Output the week numbers
    return $weekNumbers;

}
function generateAppOtp()
{
    $generator = "135792468"; // Possible digits for the OTP
    $otp = '';
    // // Iteration  6 time ti generate 6 digit OTP
    for ($i = 1; $i <= 6; $i++) {
        $otp .= substr($generator, (rand() % (strlen($generator))), 1); // Randomly select digits from the generator
    }
    return $otp;
}
function getClosingAmountByLoan($id, $type)
{
    $loanModel = $type ? App\Models\Memberloans::class : App\Models\Grouploans::class;

    $loanType = $loanModel::whereId($id)->value('loan_type');

    $closingAmount = App\Models\LoanEmisNew::where('loan_id', $id)
        ->where('loan_type', $loanType)
        ->where('is_deleted', '0')
        ->orderByDesc('emi_date')
        ->value('out_standing_amount');
    return $closingAmount;
}
function samraddhchequeentryon($daybookRefId, $id = null, $created_at, $cheque_no = null, $userType, $type, $name)
{
    // this code is modify by sourab biswas on 02-02-2024
    $chequeUpdate['is_use'] = 1;
    $chequeUpdate['status'] = 3;
    $chequeUpdate['updated_at'] = $createdAt = date('Y-m-d' . " " . date('H:i:s') . "", strtotime(convertdate($created_at)));
    if ($cheque_no) {
        $chequeData = \App\Models\SamraddhCheque::whereChequeNo($cheque_no)->first()->toArray();
        $new_value = json_encode($chequeData);
        \App\Models\SamraddhCheque::whereChequeNo($cheque_no)->update($chequeUpdate);
        $chequeDataOld = \App\Models\SamraddhCheque::whereChequeNo($cheque_no)->first()->toArray();
        $old_value = json_encode($chequeDataOld);
    } else if ($id) {
        $chequeData = \App\Models\SamraddhCheque::whereId($id)->first()->toArray();
        $new_value = json_encode($chequeData);
        \App\Models\SamraddhCheque::find($id)->update($chequeUpdate);
        $chequeDataOld = \App\Models\SamraddhCheque::whereId($id)->first()->toArray();
        $old_value = json_encode($chequeDataOld);
        $cheque_no = \App\Models\SamraddhCheque::whereId($id)->value('cheque_no');
    } else {
        return 0;
    }
    $receivedChequeTableId = $cheque_no ? \App\Models\SamraddhCheque::whereChequeNo($cheque_no)->value('id') : $id;
    $title = 'Cheque Clear';
    $u = Auth::user()->role_id == 3 ? 'Branch' : 'Admin';
    $userType = Auth::user()->role_id == 3 ? '2' : '1';
    $user = Auth::user()->username;
    $currentDateTime = \Carbon\Carbon::now()->format('d/m/Y');
    if (strpos($name, 'Payment') !== false) {
        $name = str_replace(' Payment', '', $name);
    }
    $description = "Cheque No. $cheque_no was cleared by $user via the $u Panel on $currentDateTime for $name payment.";
    return cheque_logs($type, $receivedChequeTableId, $title, $description, $new_value, $old_value, $status = 1, $daybookRefId, $userType, auth()->user()->id, $createdAt);
}

function getloanTypes()
{
    $data = \App\Models\Loans::where('loan_type', 'L')
        ->pluck('id', 'name')->toArray();
    return $data;
}
function getgroupLoanTypes()
{
    $data = \App\Models\Loans::where('loan_type', 'G')
        ->pluck('id', 'name')->toArray();
    return $data;
}
function fileI($id)
{
    $file = \App\Models\Files::whereId($id)->exists();
    if ($file) {
        $file_name = \App\Models\Files::whereId($id)->orderBy('created_at')->value('file_name');
        $path = \App\Models\Files::whereId($id)->orderBy('created_at')->value('file_path');
        // $path = str_replace('asset/', '', $path);
        // $file_name = str_replace($file_name, '', $path);
        $name = "$path/$file_name";
        $file_exists = \App\Services\ImageUpload::fileExists($name);
        if ($file_exists) {
            $href = \App\Services\ImageUpload::generatePreSignedUrl($name);
            return "<a href='$href' class='' target='_blank' >$file_name</a>";
        } else {
            return $name;
        }
    } else {
        return 'File Not Found !';
    }
}
function getInterestAmountByInvestmentId($id){
    $data = App\Models\DemandAdviceReport::whereInvestmentId($id)->value('interestAmount');   
    return $data;
}