<?php

namespace App\Helpers;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

class WhatsappTemplate
{
    /**
     * Get the Guzzle client
     *
     * @return Client
     */
    protected static function getClient()
    {
        return new Client();
    }
    /**
     * Get the configuration
     *
     * @return array
     */
    protected static function getConfig()
    {
        return [
            'access_token' => config('services.whatsapp.auth_token'),
            'business_id' => config('services.whatsapp.business_id'),
            'phone_number_id' => config('services.whatsapp.phone_id'),
        ];
    }
    /**
     * Send a request to the WhatsApp API
     *
     * @param string $method
     * @param string $url
     * @param array $data
     * @return array|bool
     */
    protected static function sendRequest($method, $url, $data = [])
    {
        $client = self::getClient();
        $config = self::getConfig();

        if (config('session.live') === 0) {
            return true;
        } else {
            try {
                $response = $client->request($method, $url, [
                    'headers' => [
                        'Authorization' => "Bearer {$config['access_token']}",
                        'Content-Type' => 'application/json',
                    ],
                    'json' => $data,
                ]);
                log::info('');
                log::info($data);
                log::info('');
                Log::info('WhatsApp API Response: ' . $response->getBody());
                return json_decode($response->getBody(), true);
            } catch (\Exception $e) {
                // Log::error('Error in WhatsApp API: ' . $e->getMessage());
                // Log::error('Error in WhatsApp API: ' . $e->getMessage() . ' in Line: ' . $e->getLine() . ' Response: ' . $e->getResponse()->getBody());
                Log::error('Error in WhatsApp API: ' . $e->getMessage() . ' in Line: ' . $e->getLine());
                return false;
            }
        }
    }
    /**
     * Send a WhatsApp message
     *
     * @param array $messageData
     * @return array|bool
     */

    public static function sendMessage(array $messageData)
    {
        $config = self::getConfig();
        // Log::info(json_encode($messageData));
        $url = "https://graph.facebook.com/v21.0/{$config['phone_number_id']}/messages";
        return self::sendRequest('POST', $url, $messageData);
    }

    /**
     * Create a WhatsApp template
     *
     * @param array $templateData
     * @return array|bool
     */
    public static function createTemplate(array $templateData)
    {
        $config = self::getConfig();
        // $url = "https://graph.facebook.com/v20.0/{$config['business_id']}/message_templates";
        $url = "https://graph.facebook.com/v21.0/{$config['business_id']}/message_templates?fields=name,status&status=REJECTED";
        return self::sendRequest('POST', $url, $templateData);
    }

    /**
     * Get all WhatsApp templates
     *
     * @return array|bool
     */
    public static function getTemplates()
    {
        $config = self::getConfig();
        $url = "https://graph.facebook.com/v20.0/{$config['business_id']}/message_templates";
        return self::sendRequest('GET', $url);
    }

    public static function getMessageStatus($messageId)
    {
        $config = self::getConfig();
        $url = "https://graph.facebook.com/v15.0/{$messageId}";

        return self::sendRequest('GET', $url);
    }

    public static function sendOtp($country_code, $phoneNumber, $otp)
    {
        // if ($country_code == '+91') {
        //     $message = [
        //         "messaging_product" => "whatsapp",
        //         "to" => $country_code . $phoneNumber,
        //         "type" => "template",
        //         "template" => [
        //             "name" => "hello_world",
        //             "language" => [
        //                 "code" => "en_US"
        //             ],
        //         ]
        //     ];
        // } else {
        $message = [
            "messaging_product" => "whatsapp",
            "recipient_type" => "individual",
            "to" => $country_code . $phoneNumber,
            "type" => "template",
            "template" => [
                "name" => "authentication_code_copy_code_button",
                "language" => [
                    "code" => "en_US",
                ],
                "components" => [
                    [
                        "type" => "body",
                        "parameters" => [
                            [
                                "type" => "text",
                                "text" => $otp,
                            ],
                        ],
                    ],
                    [
                        "type" => "button",
                        "sub_type" => "url",
                        "index" => "0",
                        "parameters" => [
                            [
                                "type" => "text",
                                "text" => $otp,
                            ],
                        ],
                    ],
                ],
            ],
        ];
        // }
        return self::sendMessage($message);
    }

    public static function sendBasicPlanMessage($country_code, $phoneNumber, $user_name, $property_link, $property_title, $property_town, $property_suburb)
    {
        $message = [
            "messaging_product" => "whatsapp",
            "to" => $country_code . $phoneNumber,
            "type" => "template",
            "template" => [
                "name" => config('services.whatsapp.template.basic'),
                "language" => [
                    "code" => "en",
                ],
                "components" => [
                    [
                        "type" => "body",
                        "parameters" => [
                            [
                                "type" => "text",
                                "text" => $user_name,
                            ],
                            [
                                "type" => "text",
                                "text" => $property_title,
                            ],
                            [
                                "type" => "text",
                                "text" => $property_town,
                            ],
                            [
                                "type" => "text",
                                "text" => $property_suburb,
                            ],
                        ],
                    ],
                    [
                        "type" => "button",
                        "sub_type" => "url",
                        "index" => "0",
                        "parameters" => [
                            [
                                "type" => "text",
                                "text" => $property_link,
                            ],
                        ],
                    ],
                ],
            ],
        ];

        return self::sendMessage($message);
    }

    public static function sendProfessionalPlanMessage($country_code, $phoneNumber, $user_name, $property_link, $property_title, $property_town, $property_suburb)
    {
        $message = [
            "messaging_product" => "whatsapp",
            "to" => $country_code . $phoneNumber,
            "type" => "template",
            "template" => [
                "name" => config('services.whatsapp.template.professional'),
                "language" => [
                    "code" => "en",
                ],
                "components" => [
                    [
                        "type" => "body",
                        "parameters" => [
                            [
                                "type" => "text",
                                "text" => $user_name,
                            ],
                            [
                                "type" => "text",
                                "text" => $property_title,
                            ],
                            [
                                "type" => "text",
                                "text" => $property_town,
                            ],
                            [
                                "type" => "text",
                                "text" => $property_suburb,
                            ],
                        ],
                    ],
                    [
                        "type" => "button",
                        "sub_type" => "url",
                        "index" => "0",
                        "parameters" => [
                            [
                                "type" => "text",
                                "text" => $property_link,
                            ],
                        ],
                    ],
                ],
            ],
        ];

        return self::sendMessage($message);
    }


    public static function agencyWelcomeMessage($country_code, $phoneNumber, $user_name, $business_name, $email, $password)
    {
        $link = asset('assets/admin/images/home.jpg');
        if (filter_var($link, FILTER_VALIDATE_URL)) {
            $message = [
                "messaging_product" => "whatsapp",
                "to" => $country_code . $phoneNumber,
                "type" => "template",
                "template" => [
                    "name" => config('services.whatsapp.welcome_template.agency'),
                    "language" => [
                        "code" => "en",
                        "policy" => "deterministic",
                    ],
                    "components" => [
                        [
                            "type" => "header",
                            "parameters" => [
                                [
                                    "type" => "image",
                                    "image" => [
                                        // "id" => "1139849371094165",
                                        "link" => $link,
                                    ],
                                ],
                            ],
                        ],
                        [
                            "type" => "body",
                            "parameters" => [
                                ["type" => "text", "text" => $user_name],
                                ["type" => "text", "text" => $business_name],
                                ["type" => "text", "text" => $email],
                                ["type" => "text", "text" => $password],
                                ["type" => "text", "text" => route('sub_login', 'agency')]
                            ],
                        ],
                    ],
                ],
            ];

            return self::sendMessage($message);
        } else {
            return "Invalid URL!";
        }
    }

    public static function agentWelcomeMessage($country_code, $phoneNumber, $user_name, $agency_name, $email, $password)
    {
        $link = asset('assets/admin/images/home.jpg');
        if (filter_var($link, FILTER_VALIDATE_URL)) {
            $message = [
                "messaging_product" => "whatsapp",
                "recipient_type" => "individual",
                "to" => $country_code . $phoneNumber,
                "type" => "template",
                "template" => [
                    "name" => config('services.whatsapp.welcome_template.agent'),
                    "language" => [
                        "code" => "en",
                        "policy" => "deterministic",
                    ],
                    "components" => [
                        [
                            "type" => "header",
                            "parameters" => [
                                [
                                    "type" => "image",
                                    "image" => [
                                        // "id" => "1139849371094165",
                                        "link" => $link,
                                    ],
                                ],
                            ],
                        ],
                        [
                            "type" => "body",
                            "parameters" => [
                                [
                                    "type" => "text",
                                    "text" => $user_name,
                                ],
                                [
                                    "type" => "text",
                                    "text" => $agency_name,
                                ],
                                [
                                    "type" => "text",
                                    "text" => $email,
                                ],
                                [
                                    "type" => "text",
                                    "text" => $password,
                                ],
                                [
                                    "type" => "text",
                                    "text" => route('sub_login', 'agent'),
                                ],
                            ],
                        ],
                    ],
                ],
            ];

            return self::sendMessage($message);
        } else {
            return "Invalid URL!";
        }
    }

    public static function landlordWelcomeMessage($country_code, $phoneNumber, $user_name)
    {
        $link = asset('assets/admin/images/home.jpg');
        if (filter_var($link, FILTER_VALIDATE_URL)) {
            $message = [
                "messaging_product" => "whatsapp",
                "recipient_type" => "individual",
                "to" => $country_code . $phoneNumber,
                "type" => "template",
                "template" => [
                    "name" => config('services.whatsapp.welcome_template.landlord'),
                    "language" => [
                        "code" => "en",
                        "policy" => "deterministic",
                    ],
                    "components" => [
                        [
                            "type" => "header",
                            "parameters" => [
                                [
                                    "type" => "image",
                                    "image" => [
                                        // "id" => "1139849371094165",
                                        "link" => $link,
                                    ],
                                ],
                            ],
                        ],
                        [
                            "type" => "body",
                            "parameters" => [
                                [
                                    "type" => "text",
                                    "text" => $user_name,
                                ],
                            ],
                        ],
                    ],
                ],
            ];

            return self::sendMessage($message);
        } else {
            return "Invalid URL!";
        }
    }

    public static function tenantWelcomeMessage($country_code, $phoneNumber, $user_name)
    {
        $link = asset('assets/admin/images/home.jpg');
        if (filter_var($link, FILTER_VALIDATE_URL)) {
            $message = [
                "messaging_product" => "whatsapp",
                "recipient_type" => "individual",
                "to" => $country_code . $phoneNumber,
                "type" => "template",
                "template" => [
                    "name" => config('services.whatsapp.welcome_template.tenant'),
                    "language" => [
                        "code" => "en",
                        "policy" => "deterministic",
                    ],
                    "components" => [
                        [
                            "type" => "header",
                            "parameters" => [
                                [
                                    "type" => "image",
                                    "image" => [
                                        // "id" => "1139849371094165",
                                        "link" => $link,
                                    ],
                                ],
                            ],
                        ],
                        [
                            "type" => "body",
                            "parameters" => [
                                [
                                    "type" => "text",
                                    "text" => $user_name,
                                ],
                            ],
                        ],
                    ],
                ],
            ];

            return self::sendMessage($message);
        } else {
            return "Invalid URL!";
        }
    }

    public static function eventMessage($country_code, $phoneNumber, $user_name, $property_name, $agent_name, $address, $date, $time, $longitude, $latitude, $event_id, $property_id)
    {
        $rescheduleUrl = URL::to("/reschedule?id=$event_id&property=$property_id");
        $message = [
            "messaging_product" => "whatsapp",
            "to" => $country_code . $phoneNumber,
            "type" => "template",
            "template" => [
                "name" => config('services.whatsapp.template.event'),
                "language" => [
                    "code" => "en",
                    "policy" => "deterministic",
                ],
                "components" => [
                    [
                        "type" => "header",
                        "parameters" => [
                            [
                                "type" => "location",
                                "location" => [
                                    "longitude" => $longitude,
                                    "latitude" => $latitude,
                                    "name" => "Property Location",
                                    "address" => $address,
                                ]
                            ],
                        ],
                    ],
                    [
                        "type" => "body",
                        "parameters" => [
                            ["type" => "text", "text" => $user_name],
                            ["type" => "text", "text" => $property_name],
                            ["type" => "text", "text" => $agent_name],
                            ["type" => "text", "text" => $address],
                            ["type" => "text", "text" => $date],
                            ["type" => "text", "text" => $time],
                            ["type" => "text", "text" => $rescheduleUrl],
                        ],
                    ],
                    [
                        "type" => "button",
                        "sub_type" => "url",
                        "index" => "0",
                        "parameters" => [
                            ["type" => "text", "text" => $event_id],
                        ],
                    ],
                    [
                        "type" => "button",
                        "sub_type" => "url",
                        "index" => "1",
                        "parameters" => [
                            ["type" => "text", "text" => $event_id],
                        ],
                    ],
                ],
            ],
        ];
        return self::sendMessage($message);
    }

    public static function getImageId(string $imageUrl)
    {
        $config = self::getConfig();
        $url = "https://graph.facebook.com/v17.0/{$config['phone_number_id']}/media";

        try {
            $response = Http::withToken($config['access_token'])
                ->post($url, [
                    'file_url' => $imageUrl,
                    'messaging_product' => 'whatsapp',
                ]);

            $responseData = $response->json();

            if ($response->successful() && isset($responseData['id'])) {
                Log::info('Media ID Retrieved: ' . $responseData['id']);
                return $responseData['id'];
            }

            Log::error('Error uploading image: ' . $response->body());
            return false;
        } catch (\Exception $e) {
            Log::error('Exception while uploading image: ' . $e->getMessage());
            return false;
        }
    }

    public static function uploadMedia($filePath, $fileType)
    {
        $phoneNumberId = '324778747387992'; // Replace with your Phone Number ID
        $accessToken = 'EAAZAgJhU0MlYBO5GXZAlZBoRHXm3IxTfMnmyTV6efHZCGQVtZBDhQqhUFlIR3ABnytFCCsXowbZCrNQpDtaikxQZCDE18BM7YGBoJJSlifHAKUtVOSxyiqJSzw7xDDFNZCOEfT2jEKAzIyZAJZC7DZCvquVtmBp3kUs9iwKOyrCVs4tmI4mPWsWudLhcr4J9E3WBaHPAAZDZD'; // Replace with your Meta Access Token
        $url = "https://graph.facebook.com/v21.0/{$phoneNumberId}/media";

        $client = new Client();

        try {
            $response = $client->post($url, [
                'headers' => [
                    'Authorization' => "Bearer {$accessToken}",
                ],
                'multipart' => [
                    [
                        'name' => 'file',
                        'contents' => fopen($filePath, 'r'),
                        'filename' => basename($filePath),
                    ],
                    [
                        'name' => 'type',
                        'contents' => $fileType,
                    ],
                    [
                        'name' => 'messaging_product',
                        'contents' => 'whatsapp',
                    ],
                ],
            ]);

            $body = json_decode($response->getBody(), true);
            log::info($body);
            return [
                'success' => true,
                'media_id' => $body['id'] ?? null,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public static function ContractSend($country_code, $phoneNumber, $name, $propertyAddress, $by_name, $dynamicContractLink)
    {
        $link = asset('assets/admin/images/contract2.jpeg');
        if (filter_var($link, FILTER_VALIDATE_URL)) {
            $message = [
                "messaging_product" => "whatsapp",
                "recipient_type" => "individual",
                "to" => $country_code . $phoneNumber,
                "type" => "template",
                "template" => [
                    "name" => config('services.whatsapp.template.tenant_contract_message'),
                    "language" => [
                        "code" => "en",
                        "policy" => "deterministic",
                    ],
                    "components" => [
                        [
                            "type" => "header",
                            "parameters" => [
                                [
                                    "type" => "image",
                                    "image" => [
                                        // "id" => "651898290583428",
                                        "link" => $link,
                                    ],
                                ],
                            ],
                        ],
                        [
                            "type" => "body",
                            "parameters" => [
                                [
                                    "type" => "text",
                                    "text" => $name,
                                ],
                                [
                                    "type" => "text",
                                    "text" => $propertyAddress,
                                ],
                                [
                                    "type" => "text",
                                    "text" => $by_name,
                                ],
                            ],
                        ],
                        [
                            "type" => "button",
                            "sub_type" => "url",
                            "index" => "0",
                            "parameters" => [
                                [
                                    "type" => "text",
                                    "text" => $dynamicContractLink
                                ],
                            ],
                        ],
                    ],
                ],
            ];
            return self::sendMessage($message);
        } else {
            return "Invalid URL!";
        }
    }

    public static function supplyPropertyMatchMessage($country_code, $phoneNumber, $user_name, $property_link)
    {
        $link = asset('assets/admin/images/contract.png');
        if (filter_var($link, FILTER_VALIDATE_URL)) {
            $message = [
                "messaging_product" => "whatsapp",
                "recipient_type" => "individual",
                "to" => $country_code . $phoneNumber,
                "type" => "template",
                "template" => [
                    "name" => config('services.whatsapp.template.agent_property_matched'),
                    "language" => [
                        "code" => "en",
                        "policy" => "deterministic",
                    ],
                    "components" => [
                        [
                            "type" => "header",
                            "parameters" => [
                                [
                                    "type" => "image",
                                    "image" => [
                                        // "id" => "1712487952682300",
                                        "link" => $link,
                                    ],
                                ],
                            ],
                        ],
                        [
                            "type" => "body",
                            "parameters" => [
                                [
                                    "type" => "text",
                                    "text" => $user_name,
                                ],
                            ],
                        ],
                        [
                            "type" => "button",
                            "sub_type" => "url",
                            "index" => "0",
                            "parameters" => [
                                [
                                    "type" => "text",
                                    "text" => $property_link
                                ],
                            ],
                        ],
                    ],
                ],
            ];

            return self::sendMessage($message);
        } else {
            return "Invalid URL!";
        }
    }

    public static function eventReschedule($country_code, $phoneNumber, $tenant_name, $property_name, $agent_name, $address, $date, $time)
    {
        $message = [
            "messaging_product" => "whatsapp",
            "to" => $country_code . $phoneNumber,
            "type" => "template",
            "template" => [
                "name" => config('services.whatsapp.template.property_reschedule'),
                "language" => [
                    "code" => "en",
                    "policy" => "deterministic",
                ],
                "components" => [
                    [
                        "type" => "body",
                        "parameters" => [
                            ["type" => "text", "text" => $agent_name],
                            ["type" => "text", "text" => $tenant_name],
                            ["type" => "text", "text" => $property_name],
                            ["type" => "text", "text" => $address],
                            ["type" => "text", "text" => $date],
                            ["type" => "text", "text" => $time],
                        ],
                    ],
                ],
            ],
        ];
        return self::sendMessage($message);
    }
}
