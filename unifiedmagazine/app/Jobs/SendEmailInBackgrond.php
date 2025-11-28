<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Record;
use App\Mail\SendEmail;
use Illuminate\Support\Facades\Mail;

class SendEmailInBackgrond implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected $to;
    protected $subject;
    protected $data;
    protected $id;
    public function __construct($to, $subject, $data, $id)
    {
        //
        $this->to = $to;
        $this->subject = $subject;
        $this->data = $data;
        $this->id = $id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Mail::to($this->to)->send(new SendEmail(($this->subject), $this->data));  
        Record::whereId($this->id)->update(['status'=>'1']);            
    }
}
