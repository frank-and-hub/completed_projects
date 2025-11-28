<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class studentMail extends Mailable
{
    use Queueable, SerializesModels;

    public $content;

    public function __construct($data)
    {
        $this->mailData = $data;
    }

    public function build()
    { 
        // return $this->view('emails.welcome')
        //             ->with(['content' => $this->content]); 
$csasontent = $this->mailData; 



                    return $this->subject($this->mailData['subject'])
                    ->view('emails.adminStudentList',compact('csasontent')); // Assuming your email view file is named welcome.blade.php
    }
}