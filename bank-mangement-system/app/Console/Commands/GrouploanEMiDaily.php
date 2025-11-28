<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Grouploans;
use App\Models\LoanEmisNew;
use DB;
use Carbon\Carbon;
use App\Http\Traits\EmiDatesTraits;

class GrouploanEMiDaily extends Command
{
    use EmiDatesTraits;

    /**
     * The name and signature of the console command.
     *use Carbon\Carbon;

     * @var string
     */
    protected $signature = 'groupemiUpdateDaily:generate';

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
        die("|h");

        $getLoans = Grouploans::where('loan_type','=',3)->where('emi_option',3)->whereIn('status',[4])->whereNotNull('approve_date')->get();
    try{
        foreach($getLoans as $loan)
        {
            $LoanCreatedDate = date('Y-m-d',strtotime($loan->approve_date));
            $LoanCreatedYear = date('Y',strtotime($loan->approve_date));
            $LoanCreatedMonth = date('m',strtotime($loan->approve_date));
            $LoanCreateDate = date('d',strtotime($loan->approve_date));
            $currentDate = date('Y-m-d');
            $CurrentDate= date('d');
            $CurrentDateYear = date('Y');
            $CurrentDateMonth = date('m');
            $daysDiff =today()->diffInDays($LoanCreatedDate);

            $nextEmiDates = $this->nextEmiDatesDaily($daysDiff,$LoanCreatedDate);

            if(array_key_exists($CurrentDate.'_'.$CurrentDateMonth.'_'.$CurrentDateYear,$nextEmiDates))
            {

                $outstandingAmount = 0;
                $roiAmount = 0;
                $principalAmount = 0;
                $deposit = 0;
                $lastOutstanding = LoanEmisNew::where('loan_id',$loan->id)->where('is_deleted','0')->where('loan_type',$loan->loan_type)->orderBy('id','desc')->first();
                if(isset($lastOutstanding->out_standing_amount))
                {
                    $roiAmount =  ((($loan->ROI) / 365) * $lastOutstanding->out_standing_amount) / 100;
                    $deposit = 0;
                    $principalAmount =$deposit - $roiAmount;
                    $outstandingAmount = ($lastOutstanding->out_standing_amount + $roiAmount);

                }
                else{
                    $roiAmount =  ((($loan->ROI) / 365) * $loan->amount) / 100;
                    $outstandingAmount = ($loan->amount + $roiAmount);
                    $principalAmount = $deposit- $roiAmount;
                    $outstandingAmount = ($loan->amount + $roiAmount);
                }
                $data = [
                    'loan_id' => $loan->id,
                    'emi_id'  => NULL,
                    'emi_option' => $loan->emi_option,
                    'out_standing_amount' => $outstandingAmount,
                    'emi_late_no_of_days' => 0,
                    'roi_amount' => $roiAmount,
                    'principal_amount' => $principalAmount,
                    'daily_wise_interest' => 0,
                    'deposit' => $deposit,
                    'emi_date' => $nextEmiDates[$CurrentDate.'_'.$CurrentDateMonth.'_'.$CurrentDateYear],
                    'emi_received_date' => NULL,
                    'penalty' => 0,
                    'loan_type' => $loan->loan_type,


                ];
                if($outstandingAmount  > 0)
                {
                    $existRecord = LoanEmisNew::WHERE('loan_id',$loan->id)->where('is_deleted','0')->where('emi_date',$nextEmiDates[$CurrentDate.'_'.$CurrentDateMonth.'_'.$CurrentDateYear])->where('loan_type',$loan->loan_type)->exists();
                    if($existRecord == false)
                    {
                        LoanEmisNew::create($data);
                    }



                }
                \Log::info("emi".$loan->id);
               // $insertData  =LoanEmisNew
            }



        }
        DB::commit();
        }   catch (\Exception $ex) {
            DB::rollback();
            return back()->with('alert', $ex->getMessage());
        }
        \Log::info("Emi Update!");

    }
}
