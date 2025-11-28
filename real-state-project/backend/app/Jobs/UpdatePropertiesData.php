<?php

namespace App\Jobs;

use App\Models\Property;
use App\Models\PropertyClientOffice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UpdatePropertiesData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $timeout = 0;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */



    public function handle()
    {
        $auth = $this->getAuthorizationHeader();

        try {
            $response = Http::withHeaders([
                "Authorization" =>  $auth
            ])->get(config("services.entegral.entegral_office_url"));

            $propertieOfficeData = $response->json();

            foreach ($propertieOfficeData as $data) {
                $this->updatePropertyOffice($data);
            }
        } catch (\Exception $e) {
            Log::error('Failed to fetch or process property office data: ' . $e->getMessage());
        }
    }
    /**
     * Get the authorization header
     */

    protected function getAuthorizationHeader()
    {
        return "Basic " . base64_encode(config('services.entegral.username') . ":" . config('services.entegral.password'));
    }
    /**
     * Update property office
     */

    protected function updatePropertyOffice($data)
    {
        try {
            $propertyOffice = PropertyClientOffice::updateOrCreate(
                ['clientOfficeID' => $data['clientOfficeID']],
                [
                    'clientOfficeID' => $data['clientOfficeID'],
                    'name' => $data['name'],
                    'tel' => $data['tel'],
                    'fax' => $data['fax'],
                    'email' => $data['email'],
                    'website' => $data['website'],
                    'logo' => $data['logo'],
                    'officereference' => $data['officereference'],
                    'sourceId' => $data['sourceId'],
                    'profile' => $data['profile'],
                    'physicalAddress' => $data['physicalAddress']
                ]
            );

            if (isset($propertyOffice)) {
                $this->updateProperties($propertyOffice);
            }
        } catch (\Exception $e) {
            Log::error('Failed to update property office: ' . $e->getMessage());
            Log::error($data);
        }
    }

    /**
     * Update properties
     */

    protected function updateProperties(PropertyClientOffice $propertyOffice)
    {
        try {
            $auth = $this->getAuthorizationHeader();

            $property_response = Http::withHeaders([
                "Authorization" =>  $auth
            ])->get(config("services.entegral.entegral_url") . "ref=" . $propertyOffice->officereference);

            $propertiesData = $property_response->json();
            $filteredProperties = array_filter($propertiesData, function ($data) {
                // return $data['propertyStatus'] === 'Rental Monthly';
                return in_array($data['propertyStatus'], ['Rental Monthly', 'Inactive']);
            });
            foreach ($filteredProperties as $data) {
                $this->updateProperty($propertyOffice, $data);
            }
        } catch (\Exception $e) {
            Log::error('Failed to update properties: ' . $e->getMessage());
        }
    }

    /**
     * Update property
     */


    protected function updateProperty(PropertyClientOffice $propertyOffice, $data)
    {
        try {
            $property = Property::updateOrCreate(
                ['clientPropertyID' => $data['clientPropertyID']],
                [
                    'client_office_id' => $propertyOffice->id,
                    'clientPropertyID' => $data['clientPropertyID'],
                    'trID' => $data['trID'],
                    'currency' => $data['currency'],
                    'price' => $data['price'],
                    'ratesAndTaxes' => $data['ratesAndTaxes'],
                    'levy' => $data['levy'],
                    'landSize' => $data['landSize'],
                    'landsizeType' => $data['landsizeType'],
                    'buildingSize' => $data['buildingSize'],
                    'buildingSizeType' => $data['buildingSizeType'],
                    'propertyType' => $data['propertyType'],
                    'propertyStatus' => $data['propertyStatus'],
                    'country' => $data['country'],
                    'province' => $data['province'],
                    'town' => $data['town'],
                    'suburb' => $data['suburb'],
                    'beds' => $data['beds'],
                    'bedroomFeatures' => $data['bedroomFeatures'],
                    'baths' => $data['baths'],
                    'bathroomFeatures' => $data['bathroomFeatures'],
                    'pool' => $data['pool'],
                    'listDate' => $data['listDate'],
                    'expiryDate' => $data['expiryDate'],
                    'occupationDate' => empty($data['occupationDate']) ? null : $data['occupationDate'],
                    'study' => $data['study'],
                    'livingAreas' => $data['livingAreas'],
                    'staffAccommodation' => $data['staffAccommodation'],
                    'carports' => $data['carports'],
                    'garages' => $data['garages'],
                    'petsAllowed' => $data['petsAllowed'],
                    'description' => $data['description'],
                    'propertyFeatures' => $data['propertyFeatures'],
                    'title' => $data['title'],
                    'priceUnit' => $data['priceUnit'],
                    'isReduced' => $data['isReduced'],
                    'isDevelopment' => $data['isDevelopment'],
                    'mandate' => $data['mandate'],
                    'furnished' => $data['furnished'],
                    'openparking' => $data['openparking'],
                    'streetNumber' => $data['streetNumber'],
                    'streetName' => $data['streetName'],
                    'unitNumber' => $data['unitNumber'],
                    'complexName' => $data['complexName'],
                    'latlng' => $data['latlng'],
                    'showOnMap' => $data['showOnMap'],
                    'action' => $data['action'],
                    'vtUrl' => $data['vtUrl'],
                ]
            );
            // Update photos and contacts
            if (isset($data['photos'])) {
                $this->updatePhotos($property, $data['photos']);
            }

            if (isset($data['contact'])) {
                $this->updateContacts($property, $data['contact']);
            }
        } catch (\Exception $e) {
            Log::error('Failed to update property: ' . $e->getMessage());
            Log::error($data);
        }
    }

    /**
     * Update photos
     */

    protected function updatePhotos(Property $property, $photos)
    {
        try {
            $property->photos()->delete();

            foreach ($photos as $photo) {
                $property->photos()->create([
                    'clientPropertyID' => $property->clientPropertyID,
                    'imgUrl' => $photo['imgUrl'],
                    'imgDescription' => $photo['imgDescription'] ?? null,
                    'isMain' => $photo['isMain'] ?? false
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to update photos: ' . $e->getMessage());
        }
    }

    /**
     * Update contacts
     */

    protected function updateContacts(Property $property, $contacts)
    {
        try {
            $property->contacts()->delete();

            foreach ($contacts as $contact) {
                $property->contacts()->create([
                    'clientPropertyID' => $property->clientPropertyID,
                    'clientOfficeID' => $contact['clientOfficeID'],
                    'officeName' => $contact['officeName'],
                    'officeTel' => $contact['officeTel'],
                    'officeFax' => $contact['officeFax'] ?? null,
                    'officeEmail' => $contact['officeEmail'],
                    'clientAgentID' => $contact['clientAgentID'],
                    'fullName' => $contact['fullName'],
                    'cell' => $contact['cell'],
                    'email' => $contact['email'],
                    'profile' => $contact['profile'] ?? null,
                    'logo' => $contact['logo'] ?? null,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to update contacts: ' . $e->getMessage());
        }
    }
}
