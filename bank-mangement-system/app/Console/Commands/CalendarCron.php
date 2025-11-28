<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\CronStoreInfo;
use Illuminate\Support\Facades\Log;
use carbon\Carbon;
use DB;
use App\Models\Event;

class CalendarCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:CalendarCron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calendar Created successfully for next year';

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
     * @return int
     */
    public function handle()
    {
        die('stop');
        try {
            // Arrays to store Saturdays, Sundays, and data for Saturday events
            $dataSaturday = array();
            $cronChannel = 'calender';
            Log::channel('calender')->info('start time - ' . Carbon::now());
            // set log File variable  to store logs of the command
            $logName = 'calender/calender-' . date('Y-m-d', strtotime(now())) . '.log';
            $this->cronService->startCron($this->signature, $logName);
            // $year = nextyear();
            $year = date('Y');
            $lastinserteddate = Event::orderBy('start_date', 'desc')->value('start_date');
            $lastEntryEvent = date('Y', strtotime($lastinserteddate));
            if ($lastEntryEvent == $year) {
                Log::channel('calender')->info('end time - ' . Carbon::now() . ', resion - calender cron already run once in ' . $year);
            } else {
                // Get the first day of the month in a given year
                $firstDayofMonth = Carbon::createFromDate($year, 1, 1);
                // Get the last day of the given year
                $lastDayofMonth = $firstDayofMonth->copy()->endOfYear();

                // Get all states from the states table
                $states = \App\Models\State::get();

                // Loop for all days in a month
                while ($firstDayofMonth->lte($lastDayofMonth)) {
                    // Condition to check if the day is a Saturday and under bank holiday
                    if ($firstDayofMonth->isSaturday()) {
                        $weekNumber = $firstDayofMonth->week;
                        if (in_array($weekNumber, getAllSaturdayCount($year))) {
                            $saturdays[] = $firstDayofMonth->copy()->toDateString();
                            // Loop through states to create data for Saturday events
                            foreach ($states as $state) {
                                $stateId = $state['id'];
                                $start_date = $firstDayofMonth->copy()->toDateString();
                                $end_date = $firstDayofMonth->copy()->toDateString();
                                $month = $firstDayofMonth->month;
                                $dataSaturday[] = [
                                    'state_id' => $state['id'],
                                    'title' => 'Saturday Holiday',
                                    'start_date' => $start_date,
                                    'end_date' => $end_date,
                                    'month' => $month,
                                ];
                                Log::channel('calender')->info("state id -  $stateId , title - Saturday Holiday , start date - $start_date , end date - $end_date , month - $month ");
                            }
                        }
                    }
                    $this->cronService->inProgress();
                    // Condition to check if the day is a Sunday
                    if ($firstDayofMonth->isSunday()) {
                        $sundays[] = $firstDayofMonth->copy()->toDateString();
                        // Loop through states to create data for Sunday events
                        foreach ($states as $state) {
                            $stateId = $state['id'];
                            $start_date = $firstDayofMonth->copy()->toDateString();
                            $end_date = $firstDayofMonth->copy()->toDateString();
                            $month = $firstDayofMonth->month;
                            $dataSaturday[] = [
                                'state_id' => $state['id'],
                                'title' => 'Sunday Holiday',
                                'start_date' => $start_date,
                                'end_date' => $end_date,
                                'month' => $month,
                            ];
                            Log::channel('calender')->info("state id -  $stateId , title - Saturday Holiday , start date - $start_date , end date - $end_date , month - $month ");
                        }
                    }
                    $firstDayofMonth->addDay();
                }
                // Insert all holiday Sundays/Saturdays for the given year into the event table

                $eventInserted = Event::insert($dataSaturday);

                // Set success or error message based on the insertion result

                Log::channel('calender')->info('end time - ' . Carbon::now());
            }
            $this->cronService->completed();
        } catch (\Exception $e) {
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
