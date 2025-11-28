<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;
use Carbon\Carbon;
use App\Models\HolidaySettings;
use App\Models\Memberloans;
use App\Models\Grouploans;
use App\Models\Notification;
use App\Models\States;
use App\Http\Traits\EmiDatesTraits;
use Illuminate\Support\Facades\Log;
use App\Services\CronStoreInfo;
use App\Services\Sms;

class EPassbookNotification extends Command
{
    use EmiDatesTraits;
    /**
     * The name and signature of the console command.
     *use Carbon\Carbon;
     * @var string
     */
    protected $signature = 'EPassbookNotification:generate';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send Loan Notification';
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(CronStoreInfo $CronStoreInfo)
    {
        parent::__construct();
        $this->cronService = $CronStoreInfo;
    }
    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        DB::begintransaction();
        try {
            $cronChannel = 'notification';
            Log::channel('notification')->info('start time - ' . Carbon::now());
            // set log File variable  to store logs of the command
            $logName = 'loan/notification-' . date('Y-m-d', strtotime(now())) . '.log';
            // Call a service to store command start process
            $this->cronService->startCron($this->signature, $logName);

            $this->loanNotification();
            $this->groupLoanNotification();
            $this->renewalNotification();
            $this->maturityNotification();
            $this->cronService->inProgress();

            $this->cronService->completed();
            Log::channel('notification')->info('end time - ' . Carbon::now());
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->cronService->errorLogs(4, $e->getMessage() . ' -Line No ' . $e->getLine() . '-File Name - ' . $e->getFile() . '', $this->signature, $cronChannel, $logName);
        }
    }
    /**
     * Summary of notificationSend
     * @param mixed $token
     * @param mixed $title
     * @param mixed $body
     * @param mixed $type
     * @param mixed $accountNumber
     * @param mixed $id
     * @return void
     */
    private function notificationSend($token, $title, $body, $type, $accountNumber, $id)
    {
        $token = $token;
        $registrationIdss = array($token);
        $data = array('type' => 'user', 'title' => $title, 'sound' => 'default', 'body' => $body, "type" => $type, "account_no" => $accountNumber, "loan_id" => $id);
        $fields = array('registration_ids' => $registrationIdss, 'data' => $data);
        $data = json_encode($fields);
        //FCM API end-point
        $url = 'https://fcm.googleapis.com/fcm/send';
        //api_key in Firebase Console -> Project Settings -> CLOUD MESSAGING -> Server key
        $server_key = 'AAAAMiX2FHk:APA91bEkiW8IBU79vW-tk8KaLct1GiCmRkYc7SwQy7Els_A6lGOSiOe9ODtqeCz99RPm1LpNfINa12xJYluWQ10oSFkWxPYMGNKDWJtkYcb9owj_7EF7rmR3fmYz4QoppAy_qKo-jkso';
        //header with content_type api key
        $headers = array(
            'Content-Type:application/json',
            'Authorization:key=' . $server_key
        );
        //CURL request to route notification to FCM connection server (provided by Google)
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $result = curl_exec($ch);
    }
    private function loanNotification()
    {
        $data = Memberloans::select('id', 'applicant_id', 'account_number', 'company_id', 'emi_option', 'emi_period', 'status', 'loan_type', 'emi_amount', 'approve_date', 'branch_id', 'customer_id')
            ->with([
                'loan' => function ($q) {
                    $q->select('id', 'name', 'loan_type');
                }
            ])
            ->with([
                'loanMember' => function ($q) {
                    $q->select('id', 'member_id', 'first_name', 'last_name', 'e_passbook_mobile_token', 'mobile_no');
                }
            ])
            ->where('status', 4)
            ->whereIn('emi_option', [1, 2])
            ->where('is_deleted', 0);
        $loant = 'L';
        $data = $data->whereHas('loan', function ($query) use ($loant) {
            $query->where('loans.loan_type', '=', $loant);
        });
        $data = $data->orderby('id', 'DESC')->get();
        if (count($data) > 0) {
            foreach ($data as $row) {
                $token = $row->loanMember->e_passbook_mobile_token ?? '';
                $applicant_id = $row->applicant_id;
                $LoanCreatedDate = date('Y-m-d', strtotime(convertDate($row->approve_date)));
                $LoanCreatedYear = date('Y', strtotime($row->approve_date));
                $LoanCreatedMonth = date('m', strtotime($row->approve_date));
                $CurrentDateYear = date('Y');
                $CurrentDateMonth = date('m');
                if ($row->emi_option == 1) {
                    $daysDiff = (($CurrentDateYear - $LoanCreatedYear) * 12) + ($CurrentDateMonth - $LoanCreatedMonth);
                    // dd(($CurrentDateYear - $LoanCreatedYear) * 12);
                    // dd($CurrentDateMonth - $LoanCreatedMonth);
                }
                if ($row->emi_option == 2) {
                    $daysDiff = today()->diffInDays($LoanCreatedDate);
                    $daysDiff = $daysDiff / 7;
                }
                if ($row->emi_option == 3) {
                    $daysDiff = today()->diffInDays($LoanCreatedDate);
                }
                // $nextEmiDates = $this->nextEmiDates($daysDiff,$LoanCreatedDate);
                $EmiDates = ($row->emi_option == 2) ? $this->nextEmiDatesWeekly($daysDiff, $LoanCreatedDate) : $this->emiDates($LoanCreatedDate, $row->emi_period);
                $currentDateINcrement = date("d_m_Y", strtotime("+1 day"));
                if (array_key_exists($currentDateINcrement, $EmiDates)) {
                    $emiDaysendget = date("d/m/Y", strtotime($EmiDates[$currentDateINcrement]));
                    $emi_date = $emiDaysendget;
                    $title = "Loan EMI Due";
                    $body = "EMI of your " . $row->loan->name . " account " . $row->account_number . " is due on " . $emi_date . ".";
                    // dd($row->loanMember->mobile_no);
                    if ($token != '') {
                        $registrationIds = array($token);
                        $this->notificationSend($token, $title, $body, "loan_due", $row->account_number, $row->id, $registrationIds);
                    }
                    $created_at = date('Y-m-d H:i:s'); // This gets the current date and time in 'Y-m-d H:i:s' format
                    $formatted_created_at = date('Y-m-d H:i:s', strtotime($created_at)); // This reformats it to 'Y-m-d H:i:s'
                    // Remove 'T' and trailing zeros
                    $formatted_created_at = str_replace(['T', '.000000Z'], [' ', ''], $formatted_created_at);
                    $notificationArr = array(
                        "user_id" => $row->customer_id,
                        "title" => $title,
                        "description" => $body,
                        "is_read" => "0",
                        "notification_type" => 4,
                        "type_id" => $row->id,
                        "company_id" => $row->company_id,
                        "panel_type" => 1,
                        'created_by_id' => null,
                        'created_at' => $formatted_created_at,
                        'created_by' => 0,
                    );

                    $sql = Notification::insert($notificationArr);
                    Log::channel('notification')->info("user_id - $row->customer_id , title - $title , notification_type - 4 , account_number - $row->account_number , company_id - $row->company_id , created_at - $formatted_created_at");
                }
            }
        }
    }
    private function groupLoanNotification()
    {
        $datagroup = Grouploans::select('id', 'applicant_id', 'branch_id', 'account_number', 'company_id', 'emi_option', 'emi_period', 'status', 'loan_type', 'emi_amount', 'approve_date', 'member_id', 'customer_id')
            ->with([
                'loan' => function ($q) {
                    $q->select('id', 'name', 'loan_type');
                }
            ])
            ->with([
                'loanMember' => function ($q) {
                    $q->select('id', 'member_id', 'first_name', 'last_name', 'e_passbook_mobile_token');
                }
            ])
            ->where('status', 4)
            ->whereIn('emi_option', [1, 2]);
        $loant = 'G';
        $datagroup = $datagroup->whereHas('loan', function ($query) use ($loant) {
            $query->where('loans.loan_type', '=', $loant);
        });
        $datagroup = $datagroup->orderby('id', 'DESC')->get();
        if (count($datagroup) > 0) {
            foreach ($datagroup as $row) {
                $token = $row->loanMember->e_passbook_mobile_token ?? '';
                $applicant_id = $row->member_id;
                $LoanCreatedDate = date('Y-m-d', strtotime(convertDate($row->approve_date)));
                $LoanCreatedYear = date('Y', strtotime($row->approve_date));
                $LoanCreatedMonth = date('m', strtotime($row->approve_date));
                $CurrentDateYear = date('Y');
                $CurrentDateMonth = date('m');
                if ($row->emi_option == 1) {
                    $daysDiff = (($CurrentDateYear - $LoanCreatedYear) * 12) + ($CurrentDateMonth - $LoanCreatedMonth);
                }
                if ($row->emi_option == 2) {
                    $daysDiff = today()->diffInDays($LoanCreatedDate);
                    $daysDiff = $daysDiff / 7;
                }
                // $nextEmiDates = $this->nextEmiDates($daysDiff,$LoanCreatedDate);
                $EmiDates = ($row->emi_option == 2) ? $this->nextEmiDatesWeekly($daysDiff, $LoanCreatedDate) : $this->emiDates($LoanCreatedDate, $row->emi_period);
                $currentDateINcrement = date("d_m_Y", strtotime("+1 day"));
                if (array_key_exists($currentDateINcrement, $EmiDates)) {
                    $emiDaysendget = date("d/m/Y", strtotime($EmiDates[$currentDateINcrement]));
                    $emi_date = $emiDaysendget;
                    $title = "Group Loan EMI Due";
                    $body = "EMI of your " . $row->loan->name . " account " . $row->account_number . " is due on " . $emi_date . ".";
                    if ($token != '') {
                        $registrationIds = array($token);
                        $this->notificationSend($token, $title, $body, "group_loan_due", $row->account_number, $row->id, $registrationIds);
                    }
                    $created_at = date('Y-m-d H:i:s'); // This gets the current date and time in 'Y-m-d H:i:s' format
                    $formatted_created_at = date('Y-m-d H:i:s', strtotime($created_at)); // This reformats it to 'Y-m-d H:i:s'
                    // Remove 'T' and trailing zeros
                    $formatted_created_at = str_replace(['T', '.000000Z'], [' ', ''], $formatted_created_at);
                    $notificationArr = array(
                        "user_id" => $row->customer_id,
                        "title" => $title,
                        "description" => $body,
                        "is_read" => "0",
                        "notification_type" => 5,
                        "type_id" => $row->id,
                        // "account_number" => $row->account_number,
                        "company_id" => $row->company_id,
                        "panel_type" => 1,
                        'created_by_id' => null,
                        'created_by' => 0,
                        'created_at' => $formatted_created_at
                    );
                    $sql = Notification::insert($notificationArr);
                    Log::channel('notification')->info("user_id - $row->customer_id , title - $title , notification_type - 5 , account_number - $row->account_number , company_id - $row->company_id , created_at - $formatted_created_at");
                }
            }
        }
    }
    private function renewalNotification()
    {

        $dataInvestment = \App\Models\Memberinvestments::select('id', 'branch_id', 'form_number', 'company_id', 'plan_id', 'tenure', 'account_number', 'deposite_amount', 'branch_id', 'associate_id', 'member_id', 'created_at', 'current_balance', 'maturity_date', 'customer_id')
            ->with([
                'member' => function ($query) {
                    $query->select('id', 'member_id', 'first_name', 'last_name', 'associate_no', 'mobile_no', 'member_id', 'address', 'e_passbook_mobile_token');
                }
            ])
            ->with([
                'plan' => function ($query) {
                    $query->select('id', 'name', 'plan_code', 'plan_category_code');
                }
            ])
            //->whereIn('plan_id',[2,3,5,6,10,11])
            ->where('is_deleted', 0)
            ->whereHas('plan', function ($query) {
                $query->where('plan_category_code', 'M');
            })
            ->where('is_mature', 1)
            ->orderby('id', 'DESC')->get();
        if (count($dataInvestment) > 0) {
            foreach ($dataInvestment as $row) {
                $token = $row->member->e_passbook_mobile_token ?? '';
                $applicant_id = $row->customer_id;
                $renewalDate = date("d/m/Y", strtotime(convertDate($row->created_at)));
                $uprenewalDate = date("Y-m-d", strtotime(convertDate($renewalDate . ' + 1 months')));
                $emiDate = date("Y-m-d", strtotime(convertDate($uprenewalDate . ' - 1 days')));
                $emiday = date("d/m/Y", strtotime(convertDate($emiDate)));
                $curdate = date('d/m/Y');
                // $currentday = date("d", strtotime($curdate));
                $currentDateINcrement = date("d_m_Y", strtotime("+1 day"));
                $EmiDates = $this->emiDatesForAPis($renewalDate, 12);
                if (array_key_exists($currentDateINcrement, $EmiDates)) {
                    // if ($curdate == $emiday) {
                    $emi_date = date("d/m/Y", strtotime(convertDate($uprenewalDate)));
                    $title = "Renewal of deposit account";
                    $body = "Renewal of your " . $row->plan->name . " account " . $row->account_number . " is due on " . $emi_date . ".";
                    if ($token != '') {
                        $this->notificationSend($token, $title, $body, "investment_renewal", $row->account_number, $row->id);
                        // not alllow update created at date
                        // \App\Models\Memberinvestments::where('id', $row->id)->update(['created_at' => $uprenewalDate]);
                    }
                    $created_at = date('Y-m-d H:i:s'); // This gets the current date and time in 'Y-m-d H:i:s' format
                    $formatted_created_at = date('Y-m-d H:i:s', strtotime($created_at)); // This reformats it to 'Y-m-d H:i:s'
                    // Remove 'T' and trailing zeros
                    $formatted_created_at = str_replace(['T', '.000000Z'], [' ', ''], $formatted_created_at);
                    $notificationArr = array(
                        "user_id" => $applicant_id,
                        "title" => $title,
                        "description" => $body,
                        "is_read" => "0",
                        "notification_type" => 2,
                        "type_id" => $row->id,
                        "company_id" => $row->company_id,
                        "panel_type" => 1,
                        'created_by_id' => $row->branch_id,
                        'created_at' => $formatted_created_at
                    );

                    $sql = Notification::insert($notificationArr);

                    Log::channel('notification')->info("user_id - $row->customer_id , title - $title , notification_type - 2 , account_number - $row->account_number , company_id - $row->company_id , created_at - $formatted_created_at");
                }
            }
        }
    }

    private function maturityNotification()
    {
        $newDateTime = Carbon::now()->addDays(1)->format('Y-m-d');
        $newDateTimeAfterSevenDays = Carbon::now()->addDays(7)->format('Y-m-d');
        $dataInvestment = \App\Models\Memberinvestments::select('id', 'branch_id', 'form_number', 'company_id', 'plan_id', 'tenure', 'account_number', 'deposite_amount', 'branch_id', 'associate_id', 'member_id', 'created_at', 'current_balance', 'maturity_date', 'customer_id')
            ->with([
                'member' => function ($q) {
                    $q->select('id', 'member_id', 'first_name', 'last_name', 'associate_no', 'mobile_no', 'member_id', 'address', 'e_passbook_mobile_token');
                }
            ])
            ->with([
                'plan' => function ($query) {
                    $query->select('id', 'name', 'plan_code');
                }
            ])
            // ->whereIn('plan_id', [2, 3, 5, 6, 10, 11])
            ->where(function ($query) use ($newDateTime, $newDateTimeAfterSevenDays) {
                $query->whereDate('maturity_date', $newDateTime)
                    ->orWhereDate('maturity_date', $newDateTimeAfterSevenDays);
            })
            ->where('is_deleted', 0)
            ->where('is_mature', 1)
            ->orderBy('id', 'DESC')
            ->get();
        if (count($dataInvestment) > 0) {
            foreach ($dataInvestment as $row) {
                $token = $row->member->e_passbook_mobile_token ?? '';
                $applicant_id = $row->customer_id;
                $renewalDate = date("d/m/Y", strtotime($row->maturity_date));
                $uprenewalDate = date("Y-m-d", strtotime(convertDate($renewalDate . ' + 1 months')));
                $emiDate = date("Y-m-d", strtotime(convertDate($renewalDate . ' - 1 day')));
                $BeforeSixDaysDate = date("Y-m-d", strtotime(convertDate($renewalDate . ' - 7 days')));
                $emiday = date("d/m/Y", strtotime(convertDate($emiDate)));
                $BeforeSixDaysDate = date("d/m/Y", strtotime(convertDate($BeforeSixDaysDate)));
                $curdate = date('d/m/Y');
                // $currentday = date("d", strtotime($curdate));
                if ($curdate == $emiday || $curdate == $BeforeSixDaysDate) {
                    $emi_date = date("d/m/Y", strtotime(convertDate($uprenewalDate)));
                    $befoir_six = date("d/m/Y", strtotime(convertDate($row->maturity_date)));
                    $title = "Maturity Info";
                    $appendsMsg = ($curdate == $emiday) ? "tomorrow" : $befoir_six;
                    $body = "Maturity of A/C " . $row->account_number . " is due on " . $appendsMsg;
                    if ($token != '') {
                        $this->notificationSend($token, $title, $body, "maturity", $row->account_number, $row->id);
                    }
                    $formatted_created_at = date('Y-m-d H:i:s');
                    $notificationArr = [
                        "user_id" => $applicant_id,
                        "title" => $title,
                        "description" => $body,
                        "is_read" => "0",
                        "notification_type" => 3,
                        "type_id" => $row->id,
                        "company_id" => $row->company_id,
                        "panel_type" => 1,
                        'created_by_id' => $row->branch_id,
                        'created_at' => $formatted_created_at,
                    ];
                    $sql = Notification::insert($notificationArr);
                    Log::channel('notification')->info("user_id - $row->customer_id , title - $title , notification_type - 3 , account_number - $row->account_number , company_id - $row->company_id , created_at - $formatted_created_at");
                }
            }
        }

    }
}
