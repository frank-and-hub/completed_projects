<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SendSMS implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
	public $details;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct( $details )
    {
	    $this->details = $details;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle( )
    {

	    if ( count($this->details['contactNumber'] ) > 0 && $this->details['message'] ) {
		    $contacts = implode(',', $this->details['contactNumber']);

		    $sms_text = urlencode( $this->details['message'] );

		    $api_url = "http://kutility.in/app/smsapi/index.php?key=".env('SMS_API_KEY')."&routeid=".env('SMS_ROUTE_ID')."&type=text&contacts=".$contacts."&senderid=".env('SMS_FROM')."&msg=".$sms_text;

		    $response = file_get_contents( $api_url);
	    } else {
		    $response = 'Contact Number Not Found!';
	    }
	    return $response;
    }
}
