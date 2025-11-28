<?php

namespace App\Helpers;

use App\Models\Admin;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class Helper
{
    /**
     * Send email using OneSignal
     *
     * @param array $email_address
     * @param string $subject
     * @param string $body
     * @return void
     */
    public static function sendOneSignalMail(array $email_address = [], string $subject, string $body)
    {
        if (config('session.live') === 1) {
            //Set Headers
            $headers = [
                "Content-Type" => "application/json; charset=utf-8",
                "Authorization" => "Basic " . config('services.onesignal.api_key'),
            ];

            $client = new Client([
                'base_uri' => 'https://onesignal.com',
                'headers' => $headers,
            ]);

            //Send Email
            $response = $client->request('POST', '/api/v1/notifications', [
                'json' => [
                    "app_id" => config('services.onesignal.app_id'),
                    "include_email_tokens" => $email_address,
                    "email_subject" => $subject,
                    "email_body" => $body,
                ],
            ]);
            json_decode($response->getBody(), true);
        }
    }

    // send otp to user
    public static function sendOtp($user, $otp)
    {
        $email = [$user->email];
        $subject = 'Verify Your Profile Update with OTP';
        $body = view('email.otp', compact('user', 'otp'))->render();
        return self::sendOneSignalMail($email, $subject, $body);
    }

    public static function sendAgentsMail($user, $agent, $property)
    {
        $email = [$agent['email']];
        $subject = 'PocketProperty: New Rental Request';
        Log::info('this is the email -> ', $email);
        // Render the view to a string
        $body = view('email.agent', compact('user', 'agent', 'property'))->render();
        return self::sendOneSignalMail($email, $subject, $body);
    }

    public static function sendAgenciesPlainMail($user, $agency)
    {
        $email = [$user['email']];
        $subject = 'PocketProperty: New Plan Renewal Request';
        // Render the view to a string

        $body = view('email.agency', compact('user', 'agency'))->render();

        return self::sendOneSignalMail($email, $subject, $body);
    }

    public static function sendReceiptMail($receiptData)
    {
        $email = [$receiptData['user_email']];
        $subject = 'PocketProperty: Subscription Receipt';

        // Render the view to a string
        $body = view('email.receipt', compact('receiptData'))->render();
        return self::sendOneSignalMail($email, $subject, $body);
    }

    public static function sendCredentialMail($credentialData)
    {
        $email = [$credentialData['email']];
        $subject = 'PocketProperty: Login Credential';

        // Render the view to a string
        $body = view('email.logincredential', compact('credentialData'))->render();
        return self::sendOneSignalMail($email, $subject, $body);
    }

    public static function defaultPassword($pass)
    {
        return $pass;
        // return 12345678;
    }

    public static function is_agencyDeleteByAdmin($id)
    {
        $admin = Admin::role('agency')->find($id);

        if ($admin->admin) {
            return false;
        }
        if ($admin->agency_agents) {
            return false;
        }
        return true;
    }

    public static function get_contract_path($id)
    {
        $property = \App\Models\InternalProperty::with('contract')->find($id);
        return ($property->contract()->value('path') ?? null);
    }

    public static function strintConversion($data)
    {
        return implode(',', $data);
    }

    /**
     * This file is located at /app/Helpers/Helper.php.
     * It appears to be part of the Helper class or namespace, which may contain utility functions
     * or methods to assist with various operations in the application.
     *
     * Note: The provided code snippet is incomplete. Ensure to review the full context of the file
     * to understand its purpose and functionality.
     */

    // Example function to get the user's location from their IP using ipinfo.io API
    public static function getUserLocationFromIP()
    {
        $ip = request()->ip();
        $url = "http://ipinfo.io/{$ip}/json";
        $response = file_get_contents($url);
        $data = json_decode($response, true);
        return $data;
    }
}
