<?php

namespace App\Http\Responses;

use App\Models\CronLog;
use App\CronDetailStoreAbstract;
use App\Services\Sms;

class CronResponse extends CronDetailStoreAbstract
{
    private $smsSent = false;

    public function storeCronDetail($request)
    {
        $cronInfo = CronLog::UpdateorCreate([
            'uuid' => $request['uuid'],
        ], $request);
    }
    public function sentCronSMS($uuid, $status)
    {
        $cronName = get_cron_name($uuid);
        $tag = get_cron_tag($uuid);
        $sms_text = "Software Cron - $cronName completed with status - $status on cron - " . date('Y-m-d H:i:s') . "  Samraddh Bestwin";
        $templateId = 1207170141602955251;
        $contactNumber = config('app.CRON_SUCCESS_NUMBERS'); // make changes by sourab on 22-01-24 for move glogabl sms numbers (check env file)
        $sendToMember = new Sms();
        if ($tag == 1) {
            $sendToMember->sendSms($contactNumber, $sms_text, $templateId);
        }
    }
}
