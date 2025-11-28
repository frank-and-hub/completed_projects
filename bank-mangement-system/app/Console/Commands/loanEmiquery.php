<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Memberloans;
use App\Models\LoanEmiNew1;
use App\Models\LoanDayBooks;
use App\Models\Loans;
use App\Models\AllHeadTransactionNew;
use App\Models\SamraddhBank;
use DB;
use Carbon\Carbon;
use App\Models\SamraddhBankDaybook;
use Illuminate\Support\Facades\Log;

class loanEmiquery extends Command
{

    /**
     * The name and signature of the console command.
     *use Carbon\Carbon;

     * @var string
     */
    protected $signature = 'loanemi:update';

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

            \App\Models\Memberloans::where('status', 4)->whereNotIn('loan_type', [3])->whereIn('account_number', [3220000013, 3211000065, 3224000005, 3224000006, 101271500164, 101271500167, 101271500166, 104271500004, 104271500003, 101271500165, 103071500059, 101171500232, 102471500037, 3213000014, 102471500038, 103171500011, 103771500001, 103771500002, 102071500101, 103171500015, 103171500014, 103171500013, 103171500012, 102871500037, 102871500038, 102171500149, 102171500148, 103571500056, 103571500055, 321671500026, 323671500014, 323271500001, 322771500015, 322971500007, 322971500006, 322971500005, 322371500051, 321271500107, 321571500091, 3223000026, 598071500041, 598071500042, 597371500071, 597371500072, 597371500074, 706571500017, 706571500016, 596571500158, 596571500157, 596571500156, 596571500155, 596571500154, 596571500153, 596171500253, 596171500252, 103071500060, 597671500088, 597871500021, 101171500233, 104271500005, 102171500150, 597271500095, 597271500094, 597271500093, 597271500092, 596271500076, 596271500077, 3220000014, 1028000031, 1012000017, 598071500044, 598071500043, 596571500160, 596571500159, 3222000018, 3222000017, 3219000007, 103871500001, 706171500208, 103671500001, 706171500207, 101971500077, 706171500206, 598071500045, 598071400001, 101271500168, 3217000005, 321771500049, 101971500079, 101971500078, 321271700240, 321271700239, 321271700238, 321271700237, 321271700236, 321271700235, 321271700234, 321271700233, 321271700232, 321271700231, 321271700230, 321271700229, 321271700228, 321271700227, 321271700226, 321271700225, 321271700224, 321271700223, 3223000027, 322371500052, 1016000007, 1016000006, 1016000005, 3215000005, 1011000090, 321771500050, 103071500061, 3217000006, 3211000066, 3223000031, 3223000030, 3223000029, 3223000028, 1015000055, 1020000013, 3211000067, 1011000091, 1015000056, 3228000015, 1012000018, 597471400001, 598071500048, 598071500047, 598071500046, 597571500076, 597571500077, 597571500078, 597571500075, 597571500074, 597371500078, 597371500079, 597371500077, 597371500076, 597371500075, 596571500161, 597471500097, 597471500096, 597471500095, 597471500094, 597471500093, 597471500092, 597471500091, 596171500255, 596171500254, 323671500015, 101171500234, 3217000007, 3228000016, 706271500088, 706271500087, 706271500086, 706171500210, 706171500209, 597671500091, 597671500090, 597671500092, 102171500151, 104171500006, 103171500016, 103171500017, 104271500006, 101971500080, 323271500002, 323271500003, 321371500093, 321371500094, 103271500024, 101171500235, 101171500236, 102071500102, 104171500007, 104271500007, 1012000019, 103571500059, 103571500058, 103571500057, 103171500018, 103171500019, 103271500025, 104171500008, 102171500152, 322471500057, 322471500058, 322471500059, 321471500011, 103171500020, 322971500008, 322971500008, 322771400001, 597171500032, 597171500033, 103071500062, 1028000032, 3219000008, 101271500169, 102071500104, 103071500063, 103071500064, 597671500093, 597671500094, 597671500095, 597671500096, 104271500009, 104271500008, 103971500001, 103971500002, 597871500022, 596171500256, 102571500003, 102571500002, 706171500212, 706171500211, 596471500173, 596471500172, 596171500257, 596171500258, 596171500259, 102071500103, 103571500060, 103571500063, 103571500062, 101971500081, 101971500085, 101971500084, 101971500083, 101971500082, 102071400002, 101571500079, 596671500084, 596671500085, 596671500083, 321271500108, 323271500004, 598071500049, 706471500022, 597271500098, 597271500096, 597271500097, 597271500099, 597271500100, 597571500081, 597571500080, 597571500079, 597471500098, 597471500099, 597471500100, 597471500101, 597471500102, 597471500103, 706671500001, 103571500061, 596571500162, 596571500163, 596571500164, 596571500165, 596571500168, 596571500166, 596571500167, 103571500064, 101971500086, 706271500089, 706271500090, 706271500091, 706271500092, 706271500094, 706271500093, 706271500095, 706271500096, 596471500174, 706171500213, 597671500097, 597671500098, 322271500114, 322271500120, 322271500115, 322271500116, 322271500117, 322271500118, 322271500119, 596271500079, 596271500078, 596171500260, 3222000019, 3224000007, 3225000001, 101571500080, 321371500098, 321371500097, 321371500096, 102071500105, 598071500050, 598071500051, 321371500095, 322971500010, 103071500065, 103571500065, 597571500082, 322971500009, 597571500083, 322871500050, 597171500034, 322071500165, 706271500097, 103171500021, 103171500022, 597471500104, 597471500105, 597471500106, 597471500107, 597871500023, 321271500109, 322071500166, 596171500263, 596171500262, 596171500261, 321971500124, 3222000020, 3227000005, 1028000033, 1028000034, 323471500001, 171271500002, 596271500083, 596271500082, 596271500081, 596271500080, 596171500265, 596171500264, 706471500023, 706471500024, 597871500024, 597871500025, 1022000002, 3235000002, 597271500101, 597271500102, 322271500122, 1011000093, 1011000092, 3214000006, 3227000006, 1011000095, 1021000017, 1011000094, 3211000068, 3213000015, 1021000018, 1011000096, 3219000009, 102571500005, 103071400003, 103171500023, 103171500024, 103771500003, 104171500009, 104271500011, 104271500012, 104271500013, 104271500014, 322971500011, 323571500007, 323671500016, 323671500017, 323671500018, 596171500266, 596171500267, 596171500268, 596571500169, 596571500170, 596571500171, 596671400009, 596671500086, 596671500087, 596671500088, 597371500080, 597371500081, 597471500108, 597471500109, 597471500110, 597471500111, 597471500112, 597571500084, 597671500102, 706171500214, 706171500215, 706171500216, 321271700245, 321271700244, 321271700243, 321271700242, 321271700241, 597671500099, 597671500101, 597671500100, 598071500052, 598071500053, 598071500054, 598071500055, 598071500056, 706271500098, 102071500106, 706171500217, 101171500237, 706271500099, 1011000097, 3212000012, 3219000010, 1011000098, 3216000012, 1011000099, 1011000100, 3216000013, 1016000008])->whereIn('company_id', [1])->chunk(2000, function ($datas) {

                foreach ($datas as $data) {

                    \App\Models\LoanDayBooks::where('account_number', $data->account_number)->where('loan_sub_type', 0)->where('is_deleted', 0)->chunk(2000, function ($records) {

                        foreach ($records as $i => $value) {

                            $d = Memberloans::where('account_number', $value->account_number)->first();
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
                            $d = Memberloans::where('account_number', $value->account_number)->first();


                            $accuredSumCR = \App\Models\AllHeadTransactionNew::where('type', '5')->where('sub_type', '545')->where('head_id', $loansDetail->ac_head_id)->where('type_transaction_id', $d->id)->where('payment_type', 'CR')->where('entry_date', '>', $strattDate)->where('entry_date', '<=', $endDate)->sum('amount');


                            $accuredSumDR = \App\Models\AllHeadTransactionNew::where('type', '5')->where('sub_type', '545')->where('head_id', $loansDetail->ac_head_id)->where('type_transaction_id', $d->id)->where('payment_type', 'DR')->where('entry_date', '>', $strattDate)->where('entry_date', '<=', $endDate)->sum('amount');




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
                                $ssbHead = \App\Models\Plans::where('company_id', $d->company_id)->where('plan_category_code', 'S')->first();

                                $paymentHead = $ssbHead->deposit_head_id;
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
                                'sub_type' => 545,
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
                                'sub_type' => 52,
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
                                'sub_type' => 52,
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
            Log::channel('loanEmiquery')->info('Run Update Emu' . $datas->account_number);

            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('alert', $ex->getMessage());
        }


    }
}
