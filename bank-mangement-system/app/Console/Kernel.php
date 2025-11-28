<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
   
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\MemberDisable::class,
        Commands\DbBackup::class,
        Commands\MoneyBackInterestTransfer::class,
        //Commands\transferssbToDebit::class,
        Commands\SendEcsReminder::class,

    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('member:disable')->everyMinute();
        $schedule->command('db:backup')->daily();
        $schedule->command('samraddhjeevaninterest:transfer')->everyMinute();
        $schedule->command('monthlyincomeschemeinteresttransfer:transfer')->everyMinute();
        $schedule->command('moneybackinteresttransfer:transfer')->everyMinute();
        $schedule->command('genrateinterestfortds:genrate')->daily();
        $schedule->command('generateTdsForInterest:genrate')->daily();
        $schedule->command('testMail:send')->everyMinute();
        $schedule->command('clearSamraddh:cache')->hourly();
        $schedule->command('checkStatus:debitCard')->everyMinute();
        $schedule->command('branchdaily:balance')->daily();
        $schedule->command('sendamnounttodebitcard:send')->hourly(); 
        $schedule->command('emiUpdate:generate')->hourly();
        
        $schedule->command('emiUpdateWeekly:generate')->hourly();
        $schedule->command('emiUpdateDaily:generate')->hourly();
        $schedule->command('commission:generate')->daily();
        $schedule->command('commission:sum')->daily();
        $schedule->command('commission:loangenerate')->daily();
        $schedule->command('dayclosing:generate')->daily();
        $schedule->command('groupemiUpdate:generate')->hourly();
        $schedule->command('groupemiUpdateWeekly:generate')->hourly();
        $schedule->command('groupemiUpdateDaily:generate')->hourly();
        $schedule->command('command:loanEntries')->daily();
        $schedule->command('command:grploanEntries')->daily();
        $schedule->command('command:membercompany')->daily();
        $schedule->command('send:new-year-wishes:generate')->yearly();
        $schedule->command('send:birthday-wishes:generate')->daily();
        $schedule->command('command:SendEcsReminder')->daily();


       // $schedule->command('transferssbToDebit:transferAmount')->daily();

    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');
       
        require base_path('routes/console.php');
    }
}
