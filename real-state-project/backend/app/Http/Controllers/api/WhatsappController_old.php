<?php

namespace App\Http\Controllers\api;

use App\Helpers\WhatsappTemplate;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\WhatsAppService;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class WhatsappController_old extends Controller
{
    public function otp_template(Request $request)
    {
        $access_token = config('services.whatsapp.auth_token');
        $business_id = config('services.whatsapp.business_id');
        $url = "https://graph.facebook.com/v20.0/{$business_id}/message_templates";
        $client = new Client();
        $templateData = [
            "name" => "authentication_code_copy_code_button",
            "language" => "en_US",
            "category" => "authentication",
            "message_send_ttl_seconds" => 60,
            "components" => [
                [
                    "type" => "body",
                    "add_security_recommendation" => true
                ],
                [
                    "type" => "footer",
                    "code_expiration_minutes" => 5
                ],
                [
                    "type" => "buttons",
                    "buttons" => [
                        [
                            "type" => "otp",
                            "otp_type" => "copy_code",
                            "text" => "Copy Code"
                        ]
                    ]
                ]
            ]
        ];

        try {
            $response = $client->post($url, [
                'headers' => [
                    'Authorization' => "Bearer {$access_token}",
                    'Content-Type' => 'application/json'
                ],
                'json' => $templateData
            ]);
            return json_decode($response->getBody(), true);
        } catch (\Exception $e) {
            Log::error('Error creating WhatsApp template: ' . $e->getMessage());
            return false;
        }
    }

    public function basic_property_alert(Request $request)
    {
        $access_token = config('services.whatsapp.auth_token');
        $business_id = config('services.whatsapp.business_id');
        $url = "https://graph.facebook.com/v20.0/{$business_id}/message_templates";
        $client = new \GuzzleHttp\Client();


        $templateData = [
            "name" => "property_alert_basic_message",
            "language" => "en_US",
            "category" => "TRANSACTIONAL",
            "components" => [
                [
                    "type" => "BODY",
                    "text" => 'Hello {{tenant_name}},\nGood news, we have a property matching the criteria of your property needs. Please follow the link below to view and apply for the property.\nThank you for choosing PocketProperty for your rental needs.'
                ],
                [
                    "type" => "BUTTONS",
                    "buttons" => [
                        [
                            "type" => "URL",
                            "text" => "View Property",
                            "url" => "{{dynamic_property_link}}"
                        ]
                    ]
                ]
            ]
        ];


        try {
            $response = $client->post($url, [
                'headers' => [
                    'Authorization' => "Bearer {$access_token}",
                    'Content-Type' => 'application/json'
                ],
                'json' => $templateData
            ]);
            return response()->json(json_decode($response->getBody(), true));
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            if ($e->hasResponse()) {
                $response = $e->getResponse();
                $responseBody = $response->getBody()->getContents();
                Log::error('Error creating WhatsApp template: ' . $responseBody);
                return response()->json(['error' => json_decode($responseBody)], $response->getStatusCode());
            } else {
                Log::error('Error creating WhatsApp template: ' . $e->getMessage());
                return response()->json(['error' => $e->getMessage()], 500);
            }
        }
    }

    public function check_template()
    {
        return WhatsappTemplate::getTemplates();
    }

    public function send_otp(Request $request)
    {
        // Validate the phone number input

        $phoneNumber = $request->phone;
        $country_code = $request->country_code;

        $phone_number_id = config('services.whatsapp.phone_id');
        $accessToken = config('services.whatsapp.auth_token');
        $url = "https://graph.facebook.com/v20.0/{$phone_number_id}/messages";
        $client = new Client();
        if ($country_code == '+91') {
            $message = [
                "messaging_product" => "whatsapp",
                "to" => $country_code . $phoneNumber,
                "type" => "template",
                "template" => [
                    "name" => "hello_world",
                    "language" => [
                        "code" => "en_US"
                    ],
                ]
            ];
        } else {
            $message = [
                "messaging_product" => "whatsapp",
                "recipient_type" => "individual",
                "to" => $country_code . $phoneNumber,
                "type" => "template",
                "template" => [
                    "name" => "authentication_code_copy_code_button",
                    "language" => [
                        "code" => "en_US"
                    ],
                    "components" => [
                        [
                            "type" => "body",
                            "parameters" => [
                                [
                                    "type" => "text",
                                    "text" => '9876'
                                ]
                            ]
                        ],
                        [
                            "type" => "button",
                            "sub_type" => "url",
                            "index" => "0",
                            "parameters" => [
                                [
                                    "type" => "text",
                                    "text" => '7896'
                                ]
                            ]
                        ]
                    ]
                ]
            ];
        }

        try {
            $response = $client->post($url, [
                'headers' => [
                    'Authorization' => "Bearer {$accessToken}",
                    'Content-Type' => 'application/json'
                ],
                'json' => $message
            ]);
            return json_decode($response->getBody(), true);
        } catch (\Exception $e) {
            Log::error('Error sending OTP via WhatsApp: ' . $e->getMessage());
            return false;
        }
    }
    public function hello_world(Request $request)
    {
        // Validate the phone number input

        $phoneNumber = $request->phone;

        $phone_number_id = config('services.whatsapp.phone_id');
        $accessToken = config('services.whatsapp.auth_token');
        $url = "https://graph.facebook.com/v20.0/{$phone_number_id}/messages";
        $client = new Client();
        $message = [
            "messaging_product" => "whatsapp",
            // "recipient_type" => "individual",
            "to" => $phoneNumber,
            "type" => "template",
            "template" => [
                "name" => "hello_world",
                "language" => [
                    "code" => "en_US"
                ],
            ]
        ];

        try {
            $response = $client->post($url, [
                'headers' => [
                    'Authorization' => "Bearer {$accessToken}",
                    'Content-Type' => 'application/json'
                ],
                'json' => $message
            ]);
            return json_decode($response->getBody(), true);
        } catch (\Exception $e) {
            Log::error('Error sending OTP via WhatsApp: ' . $e->getMessage());
            return false;
        }
    }
}
