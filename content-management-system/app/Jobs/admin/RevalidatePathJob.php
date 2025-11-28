<?php

namespace App\Jobs\admin;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RevalidatePathJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $path;
    protected $revalidateKey;
    protected $revalidateUrl;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($path)
    {
        $this->path = $path;
        $this->revalidateKey = config('services.revalidate.key');
        $this->revalidateUrl = config('services.revalidate.url');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $frontEndUrl = $this->revalidateUrl;
        $secret = $this->revalidateKey;

        $url = "$frontEndUrl/api/revalidate?secret=$secret";

        try {
            $response = Http::retry(3, 100)
                ->post($url, [
                    'path' => $this->path,
                ]);
            Log::info('Revalidate success', ['response' => $response->body()]);
        } catch (\Exception $e) {
            Log::error('Revalidate failed: ' . $e->getMessage());
        }
    }
}
