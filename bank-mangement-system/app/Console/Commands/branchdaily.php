<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Permission;
use carbon\Carbon;
use App\Services\CronStoreInfo;
use DB;

class branchdaily extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'branchdaily:balance';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Branch Crone Update as per balance';

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
        // die(); // die added by sourab on 12-04-2024
        // if(date('w') == 0){
        //     dd('Today, is sunday !');
        // }
        try {
            // getting all Branches from Branch table
            $branches = \App\Models\Branch::get(['id', 'first_login', 'manager_id', 'cash_in_hand', 'name', 'branch_code']);
            // save log before start crone to set start time and total branches.
            Log::channel('branchLimit')->info('start - total branch - ' . $branches->count('id') . ', time - ' . Carbon::now());
            $logName = 'branch/branchLimit-' . date('Y-m-d', strtotime(now())) . '.log';
            $cronChannel = 'branchLimit';

            $this->cronService->startCron($this->signature, $logName);
            $disable = 0;
            $enable = 0;
            /** all permissions for every Branch User. */
            $p = Permission::all();
            $permissionsCount = count($p->toArray());
            foreach ($branches as $branch) {
                /** all permissions for every Branch User. */
                $permissions = Permission::all();
                $branch_id = $branch->id; // get branch id
                $getCompany = \App\Models\CompanyBranch::whereBranchId($branch_id)->pluck('company_id');
                $branchBalance = 0;
                foreach ($getCompany as $k => $v) {
                    // for each company branch balance from view table.
                    $branchBalance += getbranchbankbalanceamounthelper($branch_id, $v); // get branch current balance from branch bank balance view by branch id
                }
                $fundtransfer = getfundtransferPandingAmount($branch_id); // get panding amount from branch transaction as per branch id
                $cash_in_hand = (int) $branch->cash_in_hand;
                if ($branch->first_login == '1') {
                    $branch->update(['first_login' => '0']); // when user (branch) new log in will check
                }
                $authUser = \App\Models\User::findOrFail($branch->manager_id);  // Assuming the authenticated user is an instance of the User model
                $diff_balance = ($branchBalance - $fundtransfer) <= $cash_in_hand; // check difference in branch balance or cash in hand balance
                /** crone is in progress in every branch */
                $this->cronService->inProgress();
                if ($cash_in_hand == 0) {
                    $enable++;
                    // give all permission to auth user (branch) if cash in hand amount is zero
                    $authUser->syncPermissions($permissions);
                } else {
                    if ($diff_balance) {
                        $enable++;
                        // give all permision to auth user (branch) if cash in hand amount is grater then branch current balance including fund transfer panding amount
                        $authUser->syncPermissions($permissions);
                    } else {
                        $disable++;
                        // foreach ($permissions as $permission) {
                        //     if(!in_array($permission->name,['Branch To Ho','SSB Withdraw','Branch to Bank Fund Transfer','SSB Deposit List'])){
                        //         // revoke all Permission from auth user ( branch ) accept 'Branch To Ho','SSB Withdraw','Branch to Bank Fund Transfer' if and only if branch curent amount balance + fund transfer panding amount is grater then branch cash in hand amount limit
                        //         $authUser->revokePermissionTo($permission);
                        //     }
                        // }
                        // foreach(['Branch To Ho','SSB Withdraw','Branch to Bank Fund Transfer','SSB Deposit List'] as $val){
                        //     // give only those 'Branch To Ho','SSB Withdraw','Branch to Bank Fund Transfer' Permission to auth user ( branch ) if and only if branch curent amount balance + fund transfer panding amount is grater then branch cash in hand amount limit
                        //     $authUser->givePermissionTo($val);
                        // }
                        /** as per new Update on 09-10-2023 changes are updated by Sourab
                         * for now only those below notPermissions variable named permission are
                         * removed from branch panel. for rest all permission are given.
                         */
                        $authUser->syncPermissions($permissions);
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
                }
                /** maintain log file with Branch ID,Branch Code,cash in hand Limit Amount,Current Balance and Message for define branch is enable or desable. */
                Log::channel('branchLimit')->info('Branch ID - ' . $branch_id . ' ,  Branch Code - ' . $branch->branch_code . ' ,  Limit Amount - ' . $cash_in_hand . ' , Current Balance - ' . $branchBalance . ' ,  Message - ' . (count($authUser->permissions->pluck('name')) == $permissionsCount ? 'permission enable' : 'permission disable'));
            }
            /** after complete cron set a end time with total branches with total disable branches on current cron. */
            Log::channel('branchLimit')->info('end time - ' . Carbon::now() . ' , total enable branch - ' . $enable . ' , total disable branch - ' . $disable);
            // Commit the complete code if there is no error.
            DB::commit();
            /** after complete crone the code will  update the cron_log table end_date_time column for completing log file. if there is no error then only. */
            $this->cronService->completed();
        } catch (\Exception $e) {
            /** error log in cron is use to store error message on cron_log table to get why the cron has not complete the task. with file name , line number and message. */
            $this->cronService->errorLogs(4, $e->getMessage() . ' -Line No ' . $e->getLine() . '-File Name - ' . $e->getFile() . '', $this->signature, $cronChannel, $logName);
        } finally {
            if (isset($e) && $e instanceof \Exception) {
                $this->cronService->sentCronSms(false);
            } else {
                $this->cronService->sentCronSms(true);
            }
        }
    }
}
