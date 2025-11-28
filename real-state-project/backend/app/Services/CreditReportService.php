<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class CreditReportService
{
    private $secretKey;
    private $apiKey;
    private $govSite;

    public function __construct()
    {
        $this->apiKey = config('services.getVerified.api_key');
        $this->secretKey = config('services.getVerified.webhook_secret');
        $this->govSite = config('services.getVerified.gov_site');
    }

    public function getCreditReport($idNumber)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
        ])->get($this->govSite . '/credit-report', [
            'id_number' => $idNumber,
        ]);

        if ($response->successful()) {
            return $response->json();
        } else {
            throw new \Exception('Failed to fetch credit report');
        }
    }
}
