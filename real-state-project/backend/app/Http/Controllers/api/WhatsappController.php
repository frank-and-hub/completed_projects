<?php

namespace App\Http\Controllers\api;

use App\Helpers\Helper;
use App\Helpers\WhatsappTemplate;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class WhatsappController extends Controller
{
    /**
     * This file defines the WhatsappController class, which is part of the API
     * layer of the PocketProperty backend application. It is located in the
     * "app/Http/Controllers/api" directory and is responsible for handling
     * WhatsApp-related API requests.
     *
     */

    /**
     * Class WhatsappController
     *
     * This controller handles WhatsApp-related API requests for the PocketProperty backend application.
     * It provides methods to create and send various WhatsApp message templates, check message statuses,
     * and handle email notifications. The controller utilizes the `WhatsappTemplate` and `Helper` classes
     * for template management and email functionality.
     *
     * Methods:
     *
     * - otp_template(Request $request):
     *   Creates an OTP template for WhatsApp messages.
     *
     * - basic_property_alert():
     *   Creates a basic property alert template for WhatsApp messages.
     *
     * - professional_property_alert():
     *   Creates a professional property alert template for WhatsApp messages.
     *
     * - check_template():
     *   Retrieves all available WhatsApp templates.
     *
     * - send_otp($country_code, $phoneNumber):
     *   Sends an OTP message to the specified phone number.
     *
     * - send_basic_property_alert($country_code, $phoneNumber):
     *   Sends a basic property alert message to the specified phone number.
     *
     * - send_professional_property_alert($country_code, $phoneNumber):
     *   Sends a professional property alert message to the specified phone number.
     *
     * - send_event($country_code, $phoneNumber):
     *   Sends an event notification message with event details to the specified phone number.
     *
     * - send_agentWelcome($country_code, $phoneNumber):
     *   Sends a welcome message to an agent with their credentials.
     *
     * - send_tenantWelcome($country_code, $phoneNumber):
     *   Sends a welcome message to a tenant.
     *
     * - send_landlordWelcome($country_code, $phoneNumber):
     *   Sends a welcome message to a landlord.
     *
     * - send_agencyWelcome($country_code, $phoneNumber):
     *   Sends a welcome message to an agency with their credentials.
     *
     * - send_Contrat($country_code, $phoneNumber):
     *   Sends a contract message with property details and a dynamic contract link.
     *
     * - check_status():
     *   Checks the status of a WhatsApp message using its message ID.
     *
     * - send_template(Request $request):
     *   Sends a WhatsApp message based on the specified template type and parameters.
     *
     * - sendWhatsAppMessage():
     *   Sends a WhatsApp message using the Facebook Graph API with a predefined template.
     *
     * - getMessageStatus($messageId):
     *   Retrieves the status of a WhatsApp message using its message ID.
     */

    // create otp template
    public function otp_template(Request $request)
    {
        $templateData = [
            "name" => "authentication_code_copy_code_button",
            "language" => "en_US",
            "category" => "authentication",
            "message_send_ttl_seconds" => 60,
            "components" => [
                [
                    "type" => "body",
                    "add_security_recommendation" => true,
                ],
                [
                    "type" => "footer",
                    "code_expiration_minutes" => 5,
                ],
                [
                    "type" => "buttons",
                    "buttons" => [
                        [
                            "type" => "otp",
                            "otp_type" => "copy_code",
                            "text" => "Copy Code",
                        ],
                    ],
                ],
            ],
        ];
        WhatsappTemplate::createTemplate($templateData);
    }

    // create professional property alert
    public function basic_property_alert()
    {
        $templateData = [
            "name" => "basic_property_alert",
            "language" => "en_US",
            "category" => "UTILITY",
            "components" => [
                [
                    "type" => "BODY",
                    "text" => 'Hello {{1}},\nGood news, we have a property matching the criteria of your property needs. Please follow the link below to view and apply for the property.\nThank you for choosing PocketProperty for your rental needs.',
                ],
                [
                    "type" => "BUTTONS",
                    "buttons" => [
                        [
                            "type" => "URL",
                            "text" => "View Property",
                            "url" => "http://pocket-property.pairroxz.in/{{1}}",
                        ],
                    ],
                ],
            ],
        ];
        WhatsappTemplate::createTemplate($templateData);
    }
    //  create professional property alert
    public function professional_property_alert()
    {
        $templateData = [
            "name" => "professional_property_alert",
            "language" => "en_US",
            "category" => "UTILITY",
            "components" => [
                [
                    "type" => "BODY",
                    "text" => 'Hello {{1}},\nGood news, we have matched a property meeting your criteria and submitted a contact agent request on your behalf. Please follow this link to view the property.
                    \nThank you for choosing PocketProperty for your rental needs.',
                ],
                [
                    "type" => "BUTTONS",
                    "buttons" => [
                        [
                            "type" => "URL",
                            "text" => "View Property",
                            "url" => "http://pocket-property.pairroxz.in/{{1}}",
                        ],
                    ],
                ],
            ],
        ];
        WhatsappTemplate::createTemplate($templateData);
    }
    // check template
    public function check_template()
    {
        return WhatsappTemplate::getTemplates();
    }

    // send otp
    public function send_otp($country_code, $phoneNumber)
    {
        $otp = '9876'; // OTP parameter

        WhatsappTemplate::sendOtp($country_code, $phoneNumber, $otp);
    }
    // send basic property alert
    public function send_basic_property_alert($country_code, $phoneNumber)
    {
        $user_name = 'Sushil'; // user name parameter
        $property_title = 'property_title';
        $property_town = 'property_town';
        $property_suburb = 'property_suburb';
        $property_link = 'https://laravel.com/docs/11.x/helpers#main-content'; // Property link parameter
        WhatsappTemplate::sendBasicPlanMessage($country_code, $phoneNumber, $user_name, $property_link, $property_title, $property_town, $property_suburb);
    }
    // send professional property alert
    public function send_professional_property_alert($country_code, $phoneNumber)
    {
        $user_name = 'Sushil Sharma'; // user name parameter
        $property_title = 'property_title';
        $property_town = 'property_town';
        $property_suburb = 'property_suburb';
        $property_link = '>https://laravel.com/docs/11.x/helpers#main-content'; // Property link parameter
        WhatsappTemplate::sendProfessionalPlanMessage($country_code, $phoneNumber, $user_name, $property_link, $property_title, $property_town, $property_suburb);
    }

    public function send_event($country_code, $phoneNumber)
    {
        $user_name = 'Frank key';
        $property_name = 'Rk Classes';
        $agent_name = 'Sonu Sharma';
        $address = '286- c, Laxmi Nagar Rd, Jhotwara Industrial Area, Jhotwara, Jaipur, Rajasthan 302012';
        $date = '01st Jan 2025';
        $time = '4:30 PM';
        $longitude = '26.9586204';
        $latitude = '75.7397949';
        WhatsappTemplate::eventMessage($country_code, $phoneNumber, $user_name, $property_name, $agent_name, $address, $date, $time, $longitude, $latitude, '12345','98765432');
    }
    public function send_agentWelcome($country_code, $phoneNumber)
    {
        $user_name = 'Sushil Sharma';
        $agency_name = 'Sonu Sharma';
        $email = 'Sonu@gmail.com';
        $password = 'Sonu@123';
        WhatsappTemplate::agentWelcomeMessage($country_code, $phoneNumber, $user_name, $agency_name, $email, $password);
    }
    public function send_tenantWelcome($country_code, $phoneNumber)
    {
        $user_name = 'Sushil Sharma';
        WhatsappTemplate::tenantWelcomeMessage($country_code, $phoneNumber, $user_name);
    }
    public function send_landlordWelcome($country_code, $phoneNumber)
    {
        $user_name = 'Sushil Sharma';
        WhatsappTemplate::landlordWelcomeMessage($country_code, $phoneNumber, $user_name);
    }
    public function send_agencyWelcome($country_code, $phoneNumber)
    {
        $user_name = 'Sushil Sharma';
        $business_name = 'Start Property';
        $email = 'Sushil572001@gmail.com';
        $password = '12345678';
        WhatsappTemplate::agencyWelcomeMessage($country_code, $phoneNumber, $user_name, $business_name, $email, $password);
    }
    public function send_Contrat($country_code, $phoneNumber)
    {
        $name = 'Sushil Sharma';
        $by_name = 'Sonu Sharma';
        $propertyAddress = 'See point, Western cape, Cape town, South Africa';
        $dynamicContractLink = url('/') . '/admin';
        WhatsappTemplate::ContractSend($country_code, $phoneNumber, $name, $propertyAddress, $by_name, $dynamicContractLink);
    }

    public function check_status()
    {
        $messageId = 'wamid.HBgMOTE4NDI2ODM1OTM0FQIAERgSREY4OTAxNkJDMUMwREFDRUI2AA=='; // Replace with your actual message ID

        $statusResponse = WhatsappTemplate::getMessageStatus($messageId);

        if ($statusResponse) {
            Log::info('Message Status Response: ' . json_encode($statusResponse));
        } else {
            Log::error('Failed to retrieve message status');
        }
    }

    public function send_template(Request $request)
    {
        $template = $request->template;
        $phoneNumber = $request->phone;
        $country_code = $request->country_code;
        $message_id = $request->message_id;
        if ($template == 'basic') {
            $this->send_basic_property_alert($country_code, $phoneNumber);
        }
        if ($template == 'otp') {
            $this->send_otp($country_code, $phoneNumber);
        }
        if ($template == 'professional') {
            $this->send_professional_property_alert($country_code, $phoneNumber);
        }
        if ($template == 'event') {
            $this->send_event($country_code, $phoneNumber);
        }
        if ($template == 'agent') {
            $this->send_agentWelcome($country_code, $phoneNumber);
        }
        if ($template == 'contract') {
            $this->send_Contrat($country_code, $phoneNumber);
        }
        if ($template == 'tenant') {
            // $filePath = public_path('assets/admin/images/contract2.jpeg'); // Absolute path on the local file system
            // $fileType = 'image/jpeg';

            // $response = WhatsappTemplate::uploadMedia($filePath, $fileType);

            // if ($response['success']) {
            //     echo "Media uploaded successfully! Media ID: " . $response['media_id'];
            // } else {
            //     echo "Failed to upload media: " . $response['error'];
            // }

            $this->send_tenantWelcome($country_code, $phoneNumber);

            // $this->sendWhatsAppMessage();
            // $this->getMessageStatus("wamid.HBgMOTE4NDI2ODM1OTM0FQIAERgSNzZBRUUzMTJGQTVFQkRGMDlEAA==");
        }
        if ($template == 'agency') {
            $this->send_agencyWelcome($country_code, $phoneNumber);
        }
        if ($template == 'landlord') {
            $this->send_landlordWelcome($country_code, $phoneNumber);
        }
        if ($template == 'status') {
            $this->check_status();
        }
        if ($template == 'email') {
            $user = ['name' => 'Sushil', 'email' => 'sushil@pairroxz.in', 'phone' => '9079515450'];
            $agent = ['name' => 'Pairroxz Team', 'email' => 'sourabbiswas000x@gmail.com'];
            $property = ['title' => 'Test Property', 'link' => 'https://pairroxz.com/'];
            Helper::sendAgentsMail($user, $agent, $property);
        }
        if ($template == 'agency-email') {
            $user = ['name' => 'Sushil', 'email' => 'sushil@pairroxz.in', 'phone' => '9079515450'];
            $agent = ['name' => 'Pairroxz Team', 'email' => 'sourabbiswas000x@gmail.com'];
            $property = ['title' => 'Test Property', 'link' => 'https://pairroxz.com/'];
            Helper::sendAgentsMail($user, $agent, $property);
        }
    }

    // public function email(Request $request){
    //     $user = ['name' => 'Sushil', 'email' => 'sushil@pairroxz.in', 'phone' => '9079515450'];
    //         $agent = ['name' => 'Pairroxz Team', 'email' => 'team4pairroxz@gmail.com'];
    //         $property = ['title' => 'Test Property', 'link' => 'https://pairroxz.com/'];
    //     return view('email.agent', compact('user', 'agent', 'property'));
    //     return view('email.receipt');
    // }



    public function sendWhatsAppMessage()
    {
        $url = 'https://graph.facebook.com/v17.0/324778747387992/messages'; // Replace <PHONE_NUMBER_ID>
        $accessToken = 'EAAZAgJhU0MlYBO5GXZAlZBoRHXm3IxTfMnmyTV6efHZCGQVtZBDhQqhUFlIR3ABnytFCCsXowbZCrNQpDtaikxQZCDE18BM7YGBoJJSlifHAKUtVOSxyiqJSzw7xDDFNZCOEfT2jEKAzIyZAJZC7DZCvquVtmBp3kUs9iwKOyrCVs4tmI4mPWsWudLhcr4J9E3WBaHPAAZDZD'; // Replace with your access token

        $response = Http::withToken($accessToken)->post($url, [
            "messaging_product" => "whatsapp",
            "to" => "+918426835934", // Replace with recipient's phone number in international format (e.g., 14155552671)
            "type" => "template",
            "template" => [
                "name" => "shushil_test", // Template name
                "language" => [
                    "code" => "en" // Language code
                ],
                "components" => [
                    [
                        "type" => "header",
                        "parameters" => [
                            [
                                "type" => "image",
                                "image" => [
                                    "link" => "https://scontent.whatsapp.net/v/t61.29466-34/460100231_1272614150529709_7629194232691080915_n.jpg"
                                ]
                            ]
                        ]
                    ],
                    [
                        "type" => "body",
                        "parameters" => [
                            [
                                "type" => "text",
                                "text" => "Michael" // Replace with dynamic data if needed
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        if ($response->successful()) {
            Log::info(response()->json(['message' => 'Template sent successfully!', 'response' => $response->json()]));
            return response()->json(['message' => 'Template sent successfully!', 'response' => $response->json()]);
        } else {
            log::error($response->json());
            return response()->json(['error' => 'Failed to send template.', 'response' => $response->json()]);
        }
    }

    function getMessageStatus($messageId)
    {
        $url = "https://graph.facebook.com/v17.0/wamid.HBgMOTE4NDI2ODM1OTM0FQIAERgSNzZBRUUzMTJGQTVFQkRGMDlEAA==";
        $accessToken = 'EAAZAgJhU0MlYBO5GXZAlZBoRHXm3IxTfMnmyTV6efHZCGQVtZBDhQqhUFlIR3ABnytFCCsXowbZCrNQpDtaikxQZCDE18BM7YGBoJJSlifHAKUtVOSxyiqJSzw7xDDFNZCOEfT2jEKAzIyZAJZC7DZCvquVtmBp3kUs9iwKOyrCVs4tmI4mPWsWudLhcr4J9E3WBaHPAAZDZD'; // Replace with your token

        $response = Http::withToken($accessToken)->get($url);
        log::info($response->json());
        return $response->json();
    }
}
