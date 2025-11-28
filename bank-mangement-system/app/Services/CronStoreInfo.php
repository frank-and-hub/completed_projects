<?php

namespace App\Services;

use App\Http\Responses\CronResponse;
use App\Models\CronLog;
use Str;
use DateTime;

class CronStoreInfo
{

    public function __construct()
    {
        $this->cronDetail = new CronResponse(new CronLog);
        $this->cronUuid = Str::uuid();
    }

    public function startCron($signature, $logFile)
    {


        $data = [
            'status' => 1,
            'cron_name' => $signature,
            'start_date_time' => new \DateTime('now'),
            'uuid' => $this->cronUuid,
            'log_file' => $logFile
        ];
        $this->cronDetail->storeCronDetail($data);

    }

    public function inProgress()
    {
        $data = [
            'status' => 2,
            'uuid' => $this->cronUuid,
        ];
        $this->cronDetail->storeCronDetail($data);
    }

    public function completed()
    {
        $data = [
            'status' => 3,
            'end_date_time' => new \DateTime('now'),
            'uuid' => $this->cronUuid,
        ];
        $this->cronDetail->storeCronDetail($data);
    }

    public function errorLogs($status,$msg,$signature,$channelName,$logName)
	{
		$data = [
			'cron_name' => $signature,
            'start_date_time' => new \DateTime('now'),
			'error' => 1,
			'status' => $status,
			'error_message' => $msg,
			'uuid' => $this->cronUuid,
            'log_file' => $logName
		];
		$this->cronDetail->storeCronDetail($data);
        \Log::channel($channelName)->info($msg);
	}
    public function sentCronSms($msg)
    {
        $message = $msg ? 'success' : 'field';
        $this->cronDetail->sentCronSMS($this->cronUuid, $message);
    }
    public function upToDateProgress($msg)
    {
        $data = [
            'uuid' => $this->cronUuid,
            'description' => $msg
        ];
        $this->cronDetail->storeCronDetail($data);
    }
}
