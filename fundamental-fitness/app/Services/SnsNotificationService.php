<?php

namespace App\Services;

use Aws\Sns\SnsClient;
use Aws\Exception\AwsException;
use Illuminate\Support\Facades\Log;

class SnsNotificationService
{
    protected SnsClient $sns;

    public function __construct()
    {
       
        $this->sns = new SnsClient([
            'version' => 'latest',
            'region'  => env('AWS_DEFAULT_REGION', 'us-east-1'),
            'credentials' => [
                'key'    => env('AWS_SNS_ACCESS_KEY_ID'),
                'secret' => env('AWS_SNS_SECRET_ACCESS_KEY'),
            ],
        ]);
    }

    /**
     * Send an SMS via AWS SNS.
     */
    public function sendSms(string $phoneNumber, string $message): array
    {
        try {
            $result = $this->sns->publish([
                'Message' => $message,
                'PhoneNumber' => $phoneNumber,
            ]);

            return [
                'success' => true,
                'messageId' => $result['MessageId'] ?? null,
            ];
        } catch (AwsException $e) {
            return [
                'success' => false,
                'error' => $e->getAwsErrorMessage() ?? $e->getMessage(),
            ];
        }
    }

    /**
     * Send a push notification using device token.
     */
    public function sendPushNotification(string $deviceToken, $message_data): array
    {
        try {
            $endpointArn = $this->createPlatformEndpoint($deviceToken);
            
            if (!$endpointArn) {
                return ['success' => false, 'error' => 'Failed to create endpoint.'];
            }

            // Update endpoint attributes
            $this->setEndpointAttributes($endpointArn, $deviceToken);

           $title = isset($message_data['title']) && !empty($message_data['title']) ? $message_data['title'] : 'New Notification';
           $message = isset($message_data['message']) && !empty($message_data['message']) ? $message_data['message'] : '';

            $payload = [
                'default' => $message,
                'GCM' => json_encode([
                    'data' => [
                        'title'       => $title,
                        'body'        => $message,
                        'custom_data' => 'example_data',
                        'silent'      => true
                    ]
                    ]),
                ];
            // Log::info('SNS Notification Sent: ', $payload );
            // Publish push notification
            $result = $this->sns->publish([
                'Message' => json_encode($payload),
                'MessageStructure' => 'json',
                'TargetArn' => $endpointArn,
            ]);

            return [
                'success' => true,
                'messageId' => $result['MessageId'] ?? null,
            ];
        } catch (AwsException $e) {
            return [
                'success' => false,
                'error' => $e->getAwsErrorMessage() ?? $e->getMessage(),
            ];
        }
    }

    /**
     * Register a device token in SNS and return EndpointArn.
     */
    private function createPlatformEndpoint(string $deviceToken): ?string
    {
        try {
            $result = $this->sns->createPlatformEndpoint([
                'PlatformApplicationArn' => env('AWS_SNS_PLATFORM_APPLICATION_ARN'),
                'Token' => $deviceToken,
            ]);
            return $result['EndpointArn'] ?? null;
        } catch (AwsException $e) {
            return null;
        }
    }

    /**
     * Update endpoint attributes.
     */
    private function setEndpointAttributes(string $endpointArn, string $deviceToken): void
    {
        try {
            $this->sns->setEndpointAttributes([
                'EndpointArn' => $endpointArn,
                'Attributes' => [
                    'Enabled' => 'true',
                    'Token' => $deviceToken,
                ],
            ]);
        } catch (AwsException $e) {
            // Log error if needed
        }
    }
}
