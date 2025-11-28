<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Grouploans;
use App\Models\LoanEmiNew1;
use App\Models\LoanDayBooks;
use App\Models\Loans;
use App\Models\AllHeadTransactionNew;
use App\Models\SamraddhBank;
use DB;
use Carbon\Carbon;
use App\Models\SamraddhBankDaybook;
use Illuminate\Support\Facades\Log;

class grploanemi extends Command
{

    /**
     * The name and signature of the console command.
     *use Carbon\Carbon;

     * @var string
     */
    protected $signature = 'grploanemi:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create New Emi of Current date';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $allHeadAccruedEntry = array();
        $allHeadPrincipleEntry = array();
        $allHeadpaymentEntry = array();
        $allHeadpaymentEntry2 = array();
        $calculatedDate = '';
        try {

            \App\Models\Grouploans::where('status', 4)->chunk(2000, function ($datas) {

                foreach ($datas as $data) {

                    \App\Models\LoanDayBooks::where('account_number', $data->account_number)->where('loan_sub_type', 0)->where('is_deleted', 0)->chunk(2000, function ($records) {

                        foreach ($records as $i => $value) {

                            $d = Grouploans::where('account_number', $value->account_number)->first();
                            // $d->update(['accrued_interest'=>0]);

                            $loansDetail = \App\Models\Loans::where('id', $value->loan_type)->first();
                            $calculatedDate = date('Y-m-d', strtotime($value->created_at));
                            $date = \App\Models\LoanDayBooks::where('account_number', $value->account_number)->where('loan_sub_type', 0)->where('is_deleted', 0)->whereId($value->id)->orderBY('created_at', 'desc')->first();

                            $rr = \App\Models\LoanDayBooks::where('account_number', $value->account_number)->where('loan_sub_type', 0)->where('is_deleted', 0)->where('id', '<', $value->id)->orderBY('created_at', 'desc')->first();
                            if (isset($date->created_at)) {
                                $rangeDate = date('Y-m-d', strtotime($date->created_at));
                            } else {
                                $rangeDate = $calculatedDate;
                            }
                            $state_id = getBranchDetail($value->branch_id)->state_id;
                            $currentDate = checkMonthAvailability(date('d'), date('m'), date('Y'), $state_id);
                            $currentDate = date('Y-m-d', strtotime($currentDate));
                            $dataTotalCount = DB::select('call calculate_loan_interest_update(?,?,?)', [$currentDate, $value->account_number, 1]);
                            if (isset($rr->created_at)) {
                                $strattDate = date('Y-m-d', strtotime($rr->created_at));
                                ;
                                $endDate = date('Y-m-d', strtotime($date->created_at));
                                ;
                            } else {
                                $strattDate = date('Y-m-d', strtotime($d->approve_date));
                                ;
                                $endDate = $calculatedDate;
                            }
                            $emiData = \App\Models\LoanEmiNew1::where('emi_date', $rangeDate)->where('loan_type', $value->loan_type)->where('loan_id', $value->loan_id)->where('is_deleted', '0')->first();
                            $d = Grouploans::where('account_number', $value->account_number)->first();


                            $accuredSumCR = \App\Models\AllHeadTransactionNew::where('type', '5')->where('sub_type', '546')->where('head_id', $loansDetail->ac_head_id)->where('type_transaction_id', $d->id)->where('payment_type', 'CR')->where('entry_date', '>', $strattDate)->where('entry_date', '<=', $endDate)->sum('amount');


                            $accuredSumDR = \App\Models\AllHeadTransactionNew::where('type', '5')->where('sub_type', '546')->where('head_id', $loansDetail->ac_head_id)->where('type_transaction_id', $d->id)->where('payment_type', 'DR')->where('entry_date', '>', $strattDate)->where('entry_date', '<=', $endDate)->sum('amount');




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
                                $paymentMode = 0;
                            }
                            if ($value->payment_mode == 4) {
                                $paymentHead = 56;
                                $paymentMode = 3;
                            }
                            if ($value->payment_mode == 1 || $value->payment_mode == 2 || $value->payment_mode == 3) {
                                $getSamraddhData = \App\Models\SamraddhBankDaybook::where('daybook_ref_id', $value->daybook_ref_id)->first();
                                $getHead = \App\Models\SamraddhBank::where('id', $getSamraddhData->bank_id)->first();
                                $paymentHead = $getHead->account_head_id;
                                $bankId = $getSamraddhData->bank_id;
                                $bankAcId = $getSamraddhData->bank_ac_id;
                                $paymentMode = $getSamraddhData->payment_mode;
                            }



                            $allHeadAccruedEntry = [
                                'daybook_ref_id' => $value->daybook_ref_id,
                                'branch_id' => $value->branch_id,
                                'head_id' => $loansDetail->ac_head_id,
                                'bank_id' => $bankId ?? NULL,
                                'bank_ac_id' => $bankAcId ?? NULL,
                                'type' => 5,
                                'sub_type' => 546,
                                'type_id' => $emiData->id,
                                'type_transaction_id' => $value->loan_id,
                                'associate_id' => $value->associate_id,
                                'member_id' => ($value->loan_type != 3) ? $value->applicant_id : $value->member_id,

                                'branch_id_from' => $value->branch_id,
                                'amount' => $accruedAmount,
                                'description' => $value->account_number . 'EMI collection',
                                'payment_type' => 'CR',
                                'payment_mode' => $paymentMode,
                                'currency_code' => 'INR',

                                'entry_date' => date('Y-m-d', strtotime($value->created_at)),
                                'entry_time' => date('H:i:s', strtotime($value->created_at)),
                                'created_by' => $value->created_by,
                                'created_by_id' => 0,
                                'created_at' => $value->created_at,
                                'updated_at' => $value->updated_at,
                                'company_id' => $value->company_id,
                                'is_query' => 1

                            ];

                            $allHeadPrincipleEntry = [
                                'daybook_ref_id' => $value->daybook_ref_id,
                                'branch_id' => $value->branch_id,
                                'head_id' => $loansDetail->head_id,
                                'bank_id' => $bankId ?? NULL,
                                'bank_ac_id' => $bankAcId ?? NULL,
                                'type' => 5,
                                'sub_type' => 55,
                                'type_id' => $emiData->id,
                                'type_transaction_id' => $value->loan_id,
                                'associate_id' => $value->associate_id,
                                'member_id' => ($value->loan_type != 3) ? $value->applicant_id : $value->member_id,

                                'branch_id_from' => $value->branch_id,
                                'amount' => $principalAmount,
                                'description' => $value->account_number . 'EMI collection',
                                'payment_type' => 'CR',
                                'payment_mode' => $paymentMode,
                                'currency_code' => 'INR',

                                'entry_date' => date('Y-m-d', strtotime($value->created_at)),
                                'entry_time' => date('H:i:s', strtotime($value->created_at)),
                                'created_by' => $value->created_by,
                                'created_by_id' => 0,
                                'created_at' => $value->created_at,
                                'updated_at' => $value->updated_at,
                                'company_id' => $value->company_id,
                                'is_query' => 1

                            ];
                            $allHeadpaymentEntry = [
                                'daybook_ref_id' => $value->daybook_ref_id,
                                'branch_id' => $value->branch_id,
                                'head_id' => $paymentHead,
                                'bank_id' => $bankId ?? NULL,
                                'bank_ac_id' => $bankAcId ?? NULL,
                                'type' => 5,
                                'sub_type' => 55,
                                'type_id' => $emiData->id,
                                'type_transaction_id' => $value->loan_id,
                                'associate_id' => $value->associate_id,
                                'member_id' => ($value->loan_type != 3) ? $value->applicant_id : $value->member_id,

                                'branch_id_from' => $value->branch_id,
                                'amount' => $value->deposit,
                                'description' => $value->account_number . 'EMI collection',
                                'payment_type' => 'DR',
                                'payment_mode' => $paymentMode,
                                'currency_code' => 'INR',


                                'entry_date' => date('Y-m-d', strtotime($value->created_at)),
                                'entry_time' => date('H:i:s', strtotime($value->created_at)),
                                'created_by' => $value->created_by,
                                'created_by_id' => 0,
                                'created_at' => $value->created_at,
                                'updated_at' => $value->updated_at,
                                'company_id' => $value->company_id,
                                'is_query' => 1

                            ];



                            $dataInsert1 = \App\Models\AllHeadTransactionNew::insert($allHeadAccruedEntry);
                            $dataInsert2 = \App\Models\AllHeadTransactionNew::insert($allHeadPrincipleEntry);
                            $dataInsert3 = \App\Models\AllHeadTransactionNew::insert($allHeadpaymentEntry);
                        }






                    });


                }

            });
            Log::channel('grploanemi')->info('Run Update Emu' . $datas->account_number);

            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('alert', $ex->getMessage());
        }


    }
}
