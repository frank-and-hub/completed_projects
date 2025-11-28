<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use Illuminate\Support\Facades\Mail;

use App\Services\testEmail;
class testmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'testMail:send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Mail';

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
        $email = new testEmail();
        $email->sendEmail('aman@w3care.com','Test');
    }
}
