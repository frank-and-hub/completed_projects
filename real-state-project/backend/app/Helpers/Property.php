<?php


namespace App\Helpers;

use App\Models\City;
use App\Models\Suburb;
use Illuminate\Support\Facades\Http;

class Property
{
    public static function featureColumns()
    {
        $last_array = [];

        $featureColumnsByCategory = self::featureColumnsByCategory();
        foreach ($featureColumnsByCategory as $featureColumnsByC) {
            $last_array = array_merge($last_array, $featureColumnsByC);
        }

        return $last_array;
    }

    public static function featureColumnsByCategory()
    {
        return $checkbox_columns = [
            'Amenities_and_Lifestyle'   => [
                'connectivity' => [
                    ['fiber', 'fiber'],
                    ['WiFiReady', 'WiFiReady'],
                    ['adsl', 'adsl'],
                    ['4gCoverage', '4gCoverage'],
                    ['5gCoverage', '5gCoverage'],
                ],
                'furnishing' => [
                    ['Furnishing', 'Furnishing'],
                    ['FullyFurnished', 'FullyFurnished'],
                    ['SemiFurnished', 'SemiFurnished'],
                    ['Unfurnished', 'Unfurnished']
                ],
                'kitchen_features' => [
                    ['OpenPlanKitchen', 'OpenPlanKitchen'],
                    ['PrepaidElectricity', 'PrepaidElectricity'],
                    ['SeparateKitchen', 'SeparateKitchen'],
                    ['FullyEquippedKitchen', 'FullyEquippedKitchen'],
                    ['BuiltInAppliances', 'BuiltInAppliances'],
                    ['Scullery', 'Scullery'],
                    ['Pantry', 'Pantry'],
                    ['GraniteCounterTops', 'GraniteCounterTops'],
                    ['IslandCounter', 'IslandCounter'],
                    ['InductionStove', 'InductionStove'],
                    ['DoubleOven', 'DoubleOven'],
                ],
                'cooling_heating' => [
                    ['AirConditioning', 'AirConditioning'],
                    ['CeilingFans', 'CeilingFans'],
                    ['UnderfloorHeating', 'UnderfloorHeating'],
                    ['Fireplace', 'Fireplace'],
                    ['CentralHeating', 'CentralHeating'],
                ],
                'laundry_facilities' => [
                    ['InUnitWasher', 'InUnitWasher'],
                    ['InUnitDryer', 'InUnitDryer'],
                    ['SharedLaundryRoom', 'SharedLaundryRoom'],
                    ['WashingLine', 'WashingLine'],
                    ['LaundryService', 'LaundryService'],
                ],
                'technology' => [
                    ['SmartHomeSystem', 'SmartHomeSystem'],
                    ['SmartThermosta', 'SmartThermosta'],
                    ['SmartLighting', 'SmartLighting'],
                    ['SmartLocks', 'SmartLocks'],
                    ['HomeAutomation', 'HomeAutomation'],
                ],
                'entertainment' => [
                    ['CinemaRoom', 'CinemaRoom'],
                    ['EntertainmentArea', 'EntertainmentArea'],
                    ['PrivateTheatre', 'PrivateTheatre'],
                    ['Bar', 'Bar'],
                    ['GamesRoom', 'GamesRoom'],
                ],
                'communal_areas' => [
                    ['ClubHouse', 'ClubHouse'],
                    ['SharedLounge', 'SharedLounge'],
                    ['BusinessCenter', 'BusinessCenter'],
                    ['MeetingRooms', 'MeetingRooms'],
                ]
            ],

            'Security_and_Access'   => [
                'security_features' => [
                    ['24/7Security', '24/7 Security'],
                    ['SecurityGates', 'SecurityGates'],
                    ['ElectricFence', 'ElectricFence'],
                    ['AlarmSystem', 'AlarmSystem'],
                    ['CCTV', 'CCTV'],
                    ['BiometricAccess', 'BiometricAccess'],
                    ['IntercomSystem', 'IntercomSystem'],
                    ['AccessControl', 'AccessControl'],
                    ['BurglarBars', 'BurglarBars'],
                ],
                'parking' => [
                    ['Garage', 'Garage'],
                    ['CoveredParking', 'CoveredParking'],
                    ['StreetParking', 'StreetParking'],
                    ['Carport', 'Carport'],
                    ['UndergroundParking', 'UndergroundParking'],
                    ['SecureParking', 'SecureParking'],
                    ['DisabledParking', 'DisabledParking'],
                ],
                'lease_options' => [
                    ['ShortTermLease', 'ShortTermLease'],
                    ['LongTermLease', 'LongTermLease'],
                    ['MonthToMonthLease', 'MonthToMonthLease'],
                    // ['RentToOwn', 'RentToOwn'],
                    ['EarlyTerminationAllowed', 'EarlyTerminationAllowed'],
                    ['FixedLeaseOnly', 'FixedLeaseOnly'],
                ],
                'pet_policy' => [
                    ['PetFriendly', 'PetFriendly'],
                    ['NoPetsAllowed', 'NoPetsAllowed'],
                    ['SmallPetsOnly', 'SmallPetsOnly'],
                    ['LargePetsAllowed', 'LargePetsAllowed'],
                ],
                'leisure_amenities' => [
                    ['SwimmingPool', 'SwimmingPool'],
                    ['PrivatePool', 'PrivatePool'],
                    ['Jacuzzi', 'Jacuzzi'],
                    ['Sauna', 'Sauna'],
                    ['SteamRoom', 'SteamRoom'],
                    ['TennisCourt', 'TennisCourt'],
                    ['SquashCourt', 'SquashCourt'],
                    ['BasketballCourt', 'BasketballCourt'],
                    ['OutdoorGym', 'OutdoorGym'],
                    ['GolfCourse', 'GolfCourse'],
                ],
                'building_features' => [
                    ['Elevator', 'Elevator'],
                    ['Gym', 'Gym'],
                    ['Concierge', 'Concierge'],
                    ['OnsiteManagement', 'OnsiteManagement'],
                    ['Playground', 'Playground'],
                    ['SharedCourtyard', 'SharedCourtyard'],
                ],
                'accessibility' => [
                    ['WheelchairAccess', 'WheelchairAccess'],
                    ['EntertainmentArea', 'EntertainmentArea'],
                    ['PrivateTheatre', 'PrivateTheatre'],
                    ['Ramps', 'Ramps'],
                    ['WideDoorways', 'WideDoorways'],
                    ['GamesRoom', 'GamesRoom'],
                ],
                'noise_control_features' => [
                    ['SoundProofing', 'SoundProofing'],
                    ['DoubleGlazedWindows', 'DoubleGlazedWindows'],
                ],
                'fire_safety_features' => [
                    ['SprinklerSystem', 'SprinklerSystem'],
                    ['SmokeDetectors', 'SmokeDetectors'],
                    ['CarbonMonoxideDetectors', 'CarbonMonoxideDetectors'],
                    ['FireExitPlan', 'FireExitPlan'],
                ],
            ],

            'Environment_and_Location' => [
                'location_views' => [
                    ['MountainView', 'MountainView'],
                    ['SeaView', 'SeaView'],
                    ['CityscapeView', 'CityscapeView'],
                    ['ParkView', 'ParkView'],
                    ['GardenView', 'GardenView'],
                    ['RiverView', 'RiverView'],
                    ['LakeView', 'LakeView']
                ],
                'outdoor_areas' => [
                    ['privateGarden', 'privateGarden'],
                    ['CommunalGarden', 'CommunalGarden'],
                    ['PrivatePatio', 'PrivatePatio'],
                    ['Balcony', 'Balcony'],
                    ['RoofTopTerrace', 'RoofTopTerrace'],
                    ['BraaiArea', 'BraaiArea'],
                    ['Deck', 'Deck'],
                    ['Pergola', 'Pergola'],
                ],
                'energy_efficiency' => [
                    ['SolarPanels', 'SolarPanels'],
                    ['PrepaidElectricity', 'PrepaidElectricity'],
                    ['EnergyEfficientLighting', 'EnergyEfficientLighting'],
                    ['DoubleGlazing', 'DoubleGlazing'],
                    ['RainwaterHarvesting', 'RainwaterHarvesting'],
                    ['InsulatedWindows', 'InsulatedWindows'],
                    ['GasGeyser', 'GasGeyser'],
                    ['Low-flowToilets', 'Low flowToilets'],
                ],
                'flooring' => [
                    ['TiledFloors', 'TiledFloors'],
                    ['WoodenFloors', 'WoodenFloors'],
                    ['CarpetedFloors', 'CarpetedFloors'],
                    ['LaminateFlooring', 'LaminateFlooring'],
                    ['VinylFlooring', 'VinylFlooring'],
                ],
                'proximity' => [
                    ['NearPublicTransport', 'NearPublicTransport'],
                    ['NearSchools', 'NearSchools'],
                    ['NearShoppingCenters', 'NearShoppingCenters'],
                    ['NearParks', 'NearParks'],
                    ['NearCBD', 'NearCBD'],
                    ['NearNightlife', 'NearNightlife'],
                    ['NearCoffeeShops', 'NearCoffeeShops'],
                    ['NearHospitals', 'NearHospitals'],
                ],
                'storage_space' => [
                    ['NearPublicTransport', 'NearPublicTransport'],
                    ['StorageRoom', 'StorageRoom'],
                    ['GardenShed', 'GardenShed'],
                    ['BasementStorage', 'BasementStorage'],
                    ['WalkinCloset', 'WalkinCloset'],
                    ['AtticStorage', 'AtticStorage'],
                ],
                'maintenance_services' => [
                    ['GardenMaintenance', 'GardenMaintenance'],
                    ['PoolMaintenance', 'PoolMaintenance'],
                    ['CleaningServices', 'CleaningServices'],
                    ['LaundryService', 'LaundryService'],
                ],
                'water_features' => [
                    ['Borehole', 'Borehole'],
                    ['RainwaterHarvesting', 'RainwaterHarvesting'],
                    ['WaterTanks', 'WaterTanks'],
                    ['GreywaterSystem', 'GreywaterSystem'],
                    ['IrrigationSystem', 'IrrigationSystem'],
                ],
                'location_features' => [
                    ['Seaside', 'Seaside'],
                    ['Urban', 'Urban'],
                    ['Suburban', 'Suburban'],
                    ['Rural', 'Rural'],
                    ['GatedCommunity', 'GatedCommunity'],
                    ['QuietNeighborhood', 'QuietNeighborhood'],
                ],
            ],
        ];
    }

    public static function suburbsInsertByGoogle(City $city)
    {

        $city_name = $city->city_name;
        $state_name = $city->province->province_name;
        $country_name = $city->country->name;

        $suburb_records_insert = [
            'province_id'   => $city->province_id,
            'city_id'       => $city->id,
            'created_at'    => now(),
            'updated_at'    => now()
        ];
        $suburb_records_inserts = [];

        $apiKey = config('constants.GOOGLE_MAP_BACKEND_KEY');
        $address = "{$city_name}, {$state_name}, {$country_name}";
        $url = "https://maps.googleapis.com/maps/api/place/textsearch/json";


        $searchOnly = [
            "sublocality_level_1",
            "sublocality",
            "political"
        ];

        $notSearchOnly = [
            'sublocality_level_2'
        ];

        $array = [];
        $nextPageToken = null;

        do {
            // Add the nextPageToken to the request if it's available
            $params = [
                'query' => 'sublocality in ' . $address,
                'location'  => $address,
                'radius'    => 5000,
                'key' => $apiKey,
            ];

            if ($nextPageToken) {
                $params['pagetoken'] = $nextPageToken;
            }

            $response = Http::get($url, $params);

            if ($response->ok()) {
                $results = $response->json()['results'];
                foreach ($results as $result) {
                    $searchOnly_count = count(array_intersect($searchOnly, $result['types'])) > 0;
                    $NotSearchOnly_count = count(array_intersect($notSearchOnly, $result['types'])) == 0;
                    if (($searchOnly_count)  &&   $NotSearchOnly_count) {

                        if (!in_array($result['name'], $array)) {
                            $array[] = $result['name'];

                            $suburb_records_insert['suburb_name'] = strtolower($result['name']);
                            Suburb::firstOrcreate($suburb_records_insert);
                        }
                    }
                }
            }

            $nextPageToken = $response->json()['next_page_token'] ?? null;

            if ($nextPageToken) {
                sleep(2);
            }
        } while ($nextPageToken);

        return $array;
    }

    public static function featureColumnsByCategoryAPI()
    {
        return [
            'agent_phone_number' => 'Enter agent phone number',
            'Basic_Information' => [
                'landSize' => [
                    'type' => 'number'
                ],
                'buildingSize' => [
                    'type' => 'number'
                ],
                'title' => [
                    'type' => 'string'
                ],
                'propertyType' => [
                    'type' => 'string',
                    'enum' => [
                        'House',
                        'Garden Cottage',
                        'Townhouse',
                        'Apartment',
                        'Business',
                        'Cluster',
                        'Garden Cottage',
                        'Hotel',
                        'Industrial',
                        'Mixed Use',
                        'Office',
                        'Penthouse',
                        'Retail',
                        'Townhouse'
                    ]
                ],
                'propertyStatus' => [
                    'type' => 'string',
                    'enum' => [
                        'Rental Monthly'
                    ],
                ],
                'bedroom' => [
                    'type' => 'string',
                    'enum' => [
                        '1',
                        '2',
                        '3',
                        '4',
                        '+5'
                    ],
                ],
                'bathroom' =>  [
                    'type' => 'string',
                    'enum' => [
                        '1',
                        '2',
                        '3',
                        '4',
                        '+5'
                    ],
                ],
                'description' => [
                    'type' => 'string',
                ],
            ],
            'Location_and_Address' => [
                'country' => [
                    'type' => 'string'
                ],
                'state' => [
                    'type' => 'string'
                ],
                'city' => [
                    'type' => 'string'
                ],
                'suburb' => [
                    'type' => 'string'
                ],
                'streetNumber' => [
                    'type' => 'string'
                ],
                'streetName' => [
                    'type' => 'string'
                ],
                'unitNumber' => [
                    'type' => 'string'
                ],
                'complexName' => [
                    'type' => 'string'
                ],
            ],
            'Financial' => [
                'currency' => [
                    'type' => 'string'
                ],
                'price' => [
                    'type' => 'number'
                ],
                'ratesAndTaxes' => [
                    'type' => 'number'
                ],
                'levy' => [
                    'type' => 'number'
                ],
                'depositRequired' => [
                    'type' => 'number'
                ],
                'leasePeriod' => [
                    'type' => 'number'
                ],
                'isReduced' => [
                    'type' => 'boolean'
                ],
            ],
            'Property_Images' => [
                'ismain_image' => [
                    'type' => 'file'
                ],
                'files[]' => [
                    'type' => 'file'
                ]
            ],
            'Map_Location' => [
                'latitude' => [
                    'type' => 'string'
                ],
                'longitude' => [
                    'type' => 'string'
                ]
            ],
            'Rental_Property_Features' => [
                'Amenities_and_Lifestyle'   => [
                    'connectivity' => [
                        'fiber' => [
                            'type' => 'boolean'
                        ],
                        'WiFiReady' => [
                            'type' => 'boolean'
                        ],
                        'adsl' => [
                            'type' => 'boolean'
                        ],
                        '4gCoverage' => [
                            'type' => 'boolean'
                        ],
                        '5gCoverage' => [
                            'type' => 'boolean'
                        ],
                    ],
                    'furnishing' => [
                        'Furnishing' => [
                            'type' => 'boolean'
                        ],
                        'FullyFurnished' => [
                            'type' => 'boolean'
                        ],
                        'SemiFurnished' => [
                            'type' => 'boolean'
                        ],
                        'Unfurnished' => [
                            'type' => 'boolean'
                        ],
                    ],
                    'kitchen_features' => [
                        'OpenPlanKitchen' => [
                            'type' => 'boolean'
                        ],
                        'PrepaidElectricity' => [
                            'type' => 'boolean'
                        ],
                        'SeparateKitchen' => [
                            'type' => 'boolean'
                        ],
                        'FullyEquippedKitchen' => [
                            'type' => 'boolean'
                        ],
                        'BuiltInAppliances' => [
                            'type' => 'boolean'
                        ],
                        'Scullery' => [
                            'type' => 'boolean'
                        ],
                        'Pantry' => [
                            'type' => 'boolean'
                        ],
                        'GraniteCounterTops' => [
                            'type' => 'boolean'
                        ],
                        'IslandCounter' => [
                            'type' => 'boolean'
                        ],
                        'InductionStove' => [
                            'type' => 'boolean'
                        ],
                        'DoubleOven' => [
                            'type' => 'boolean'
                        ],
                    ],
                    'cooling_heating' => [
                        'AirConditioning' => [
                            'type' => 'boolean'
                        ],
                        'CeilingFans' => [
                            'type' => 'boolean'
                        ],
                        'UnderfloorHeating' => [
                            'type' => 'boolean'
                        ],
                        'Fireplace' => [
                            'type' => 'boolean'
                        ],
                        'CentralHeating' => [
                            'type' => 'boolean'
                        ],
                    ],
                    'laundry_facilities' => [
                        'InUnitWasher' => [
                            'type' => 'boolean'
                        ],
                        'InUnitDryer' => [
                            'type' => 'boolean'
                        ],
                        'SharedLaundryRoom' => [
                            'type' => 'boolean'
                        ],
                        'WashingLine' => [
                            'type' => 'boolean'
                        ],
                        'LaundryService' => [
                            'type' => 'boolean'
                        ],
                    ],
                    'technology' => [
                        'SmartHomeSystem' => [
                            'type' => 'boolean'
                        ],
                        'SmartThermosta' => [
                            'type' => 'boolean'
                        ],
                        'SmartLighting' => [
                            'type' => 'boolean'
                        ],
                        'SmartLocks' => [
                            'type' => 'boolean'
                        ],
                        'HomeAutomation' => [
                            'type' => 'boolean'
                        ],
                    ],
                    'entertainment' => [
                        'CinemaRoom' => [
                            'type' => 'boolean'
                        ],
                        'EntertainmentArea' => [
                            'type' => 'boolean'
                        ],
                        'PrivateTheatre' => [
                            'type' => 'boolean'
                        ],
                        'Bar' => [
                            'type' => 'boolean'
                        ],
                        'GamesRoom' => [
                            'type' => 'boolean'
                        ],
                    ],
                    'communal_areas' => [
                        'ClubHouse' => [
                            'type' => 'boolean'
                        ],
                        'SharedLounge' => [
                            'type' => 'boolean'
                        ],
                        'BusinessCenter' => [
                            'type' => 'boolean'
                        ],
                        'MeetingRooms' => [
                            'type' => 'boolean'
                        ],
                    ]
                ],
                'Security_and_Access'   => [
                    'security_features' => [
                        '24/7Security' => [
                            'type' => 'boolean'
                        ],
                        'SecurityGates' => [
                            'type' => 'boolean'
                        ],
                        'ElectricFence' => [
                            'type' => 'boolean'
                        ],
                        'AlarmSystem' => [
                            'type' => 'boolean'
                        ],
                        'CCTV' => [
                            'type' => 'boolean'
                        ],
                        'BiometricAccess' => [
                            'type' => 'boolean'
                        ],
                        'IntercomSystem' => [
                            'type' => 'boolean'
                        ],
                        'AccessControl' => [
                            'type' => 'boolean'
                        ],
                        'BurglarBars' => [
                            'type' => 'boolean'
                        ],
                    ],
                    'parking' => [
                        'Garage' => [
                            'type' => 'boolean'
                        ],
                        'CoveredParking' => [
                            'type' => 'boolean'
                        ],
                        'StreetParking' => [
                            'type' => 'boolean'
                        ],
                        'Carport' => [
                            'type' => 'boolean'
                        ],
                        'UndergroundParking' => [
                            'type' => 'boolean'
                        ],
                        'SecureParking' => [
                            'type' => 'boolean'
                        ],
                        'DisabledParking' => [
                            'type' => 'boolean'
                        ],
                    ],
                    'lease_options' => [
                        'ShortTermLease' => [
                            'type' => 'boolean'
                        ],
                        'LongTermLease' => [
                            'type' => 'boolean'
                        ],
                        'MonthToMonthLease' => [
                            'type' => 'boolean'
                        ],
                        'EarlyTerminationAllowed' => [
                            'type' => 'boolean'
                        ],
                        'FixedLeaseOnly' => [
                            'type' => 'boolean'
                        ],
                    ],
                    'pet_policy' => [
                        'PetFriendly' => [
                            'type' => 'boolean'
                        ],
                        'NoPetsAllowed' => [
                            'type' => 'boolean'
                        ],
                        'SmallPetsOnly' => [
                            'type' => 'boolean'
                        ],
                        'LargePetsAllowed' => [
                            'type' => 'boolean'
                        ],
                    ],
                    'leisure_amenities' => [
                        'SwimmingPool' => [
                            'type' => 'boolean'
                        ],
                        'PrivatePool' => [
                            'type' => 'boolean'
                        ],
                        'Jacuzzi' => [
                            'type' => 'boolean'
                        ],
                        'Sauna' => [
                            'type' => 'boolean'
                        ],
                        'SteamRoom' => [
                            'type' => 'boolean'
                        ],
                        'TennisCourt' => [
                            'type' => 'boolean'
                        ],
                        'SquashCourt' => [
                            'type' => 'boolean'
                        ],
                        'BasketballCourt' => [
                            'type' => 'boolean'
                        ],
                        'OutdoorGym' => [
                            'type' => 'boolean'
                        ],
                        'GolfCourse' => [
                            'type' => 'boolean'
                        ],
                    ],
                    'building_features' => [
                        'Elevator' => [
                            'type' => 'boolean'
                        ],
                        'Gym' => [
                            'type' => 'boolean'
                        ],
                        'Concierge' => [
                            'type' => 'boolean'
                        ],
                        'OnsiteManagement' => [
                            'type' => 'boolean'
                        ],
                        'Playground' => [
                            'type' => 'boolean'
                        ],
                        'SharedCourtyard' => [
                            'type' => 'boolean'
                        ],
                    ],
                    'accessibility' => [
                        'WheelchairAccess' => [
                            'type' => 'boolean'
                        ],
                        'EntertainmentArea' => [
                            'type' => 'boolean'
                        ],
                        'PrivateTheatre' => [
                            'type' => 'boolean'
                        ],
                        'Ramps' => [
                            'type' => 'boolean'
                        ],
                        'WideDoorways' => [
                            'type' => 'boolean'
                        ],
                        'GamesRoom' => [
                            'type' => 'boolean'
                        ],
                    ],
                    'noise_control_features' => [
                        'SoundProofing' => [
                            'type' => 'boolean'
                        ],
                        'DoubleGlazedWindows' => [
                            'type' => 'boolean'
                        ],
                    ],
                    'fire_safety_features' => [
                        'SprinklerSystem' => [
                            'type' => 'boolean'
                        ],
                        'SmokeDetectors' => [
                            'type' => 'boolean'
                        ],
                        'CarbonMonoxideDetectors' => [
                            'type' => 'boolean'
                        ],
                        'FireExitPlan' => [
                            'type' => 'boolean'
                        ],
                    ],
                ],
                'Environment_and_Location' => [
                    'location_views' => [
                        'MountainView' => [
                            'type' => 'boolean'
                        ],
                        'SeaView' => [
                            'type' => 'boolean'
                        ],
                        'CityscapeView' => [
                            'type' => 'boolean'
                        ],
                        'ParkView' => [
                            'type' => 'boolean'
                        ],
                        'GardenView' => [
                            'type' => 'boolean'
                        ],
                        'RiverView' => [
                            'type' => 'boolean'
                        ],
                        'LakeView' => [
                            'type' => 'boolean'
                        ],
                    ],
                    'outdoor_areas' => [
                        'privateGarden' => [
                            'type' => 'boolean'
                        ],
                        'CommunalGarden' => [
                            'type' => 'boolean'
                        ],
                        'PrivatePatio' => [
                            'type' => 'boolean'
                        ],
                        'Balcony' => [
                            'type' => 'boolean'
                        ],
                        'RoofTopTerrace' => [
                            'type' => 'boolean'
                        ],
                        'BraaiArea' => [
                            'type' => 'boolean'
                        ],
                        'Deck' => [
                            'type' => 'boolean'
                        ],
                        'Pergola' => [
                            'type' => 'boolean'
                        ],
                    ],
                    'energy_efficiency' => [
                        'SolarPanels' => [
                            'type' => 'boolean'
                        ],
                        'PrepaidElectricity' => [
                            'type' => 'boolean'
                        ],
                        'EnergyEfficientLighting' => [
                            'type' => 'boolean'
                        ],
                        'DoubleGlazing' => [
                            'type' => 'boolean'
                        ],
                        'RainwaterHarvesting' => [
                            'type' => 'boolean'
                        ],
                        'InsulatedWindows' => [
                            'type' => 'boolean'
                        ],
                        'GasGeyser' => [
                            'type' => 'boolean'
                        ],
                        'Low-flowToilets',
                        [
                            'type' => 'boolean'
                        ],
                    ],
                    'flooring' => [
                        'TiledFloors' => [
                            'type' => 'boolean'
                        ],
                        'WoodenFloors' => [
                            'type' => 'boolean'
                        ],
                        'CarpetedFloors' => [
                            'type' => 'boolean'
                        ],
                        'LaminateFlooring' => [
                            'type' => 'boolean'
                        ],
                        'VinylFlooring' => [
                            'type' => 'boolean'
                        ],
                    ],
                    'proximity' => [
                        'NearPublicTransport' => [
                            'type' => 'boolean'
                        ],
                        'NearSchools' => [
                            'type' => 'boolean'
                        ],
                        'NearShoppingCenters' => [
                            'type' => 'boolean'
                        ],
                        'NearParks' => [
                            'type' => 'boolean'
                        ],
                        'NearCBD' => [
                            'type' => 'boolean'
                        ],
                        'NearNightlife' => [
                            'type' => 'boolean'
                        ],
                        'NearCoffeeShops' => [
                            'type' => 'boolean'
                        ],
                        'NearHospitals' => [
                            'type' => 'boolean'
                        ],
                    ],
                    'storage_space' => [
                        'NearPublicTransport' => [
                            'type' => 'boolean'
                        ],
                        'StorageRoom' => [
                            'type' => 'boolean'
                        ],
                        'GardenShed' => [
                            'type' => 'boolean'
                        ],
                        'BasementStorage' => [
                            'type' => 'boolean'
                        ],
                        'WalkinCloset' => [
                            'type' => 'boolean'
                        ],
                        'AtticStorage' => [
                            'type' => 'boolean'
                        ],
                    ],
                    'maintenance_services' => [
                        'GardenMaintenance' => [
                            'type' => 'boolean'
                        ],
                        'PoolMaintenance' => [
                            'type' => 'boolean'
                        ],
                        'CleaningServices' => [
                            'type' => 'boolean'
                        ],
                        'LaundryService' => [
                            'type' => 'boolean'
                        ],
                    ],
                    'water_features' => [
                        'Borehole' => [
                            'type' => 'boolean'
                        ],
                        'RainwaterHarvesting' => [
                            'type' => 'boolean'
                        ],
                        'WaterTanks' => [
                            'type' => 'boolean'
                        ],
                        'GreywaterSystem' => [
                            'type' => 'boolean'
                        ],
                        'IrrigationSystem' => [
                            'type' => 'boolean'
                        ],
                    ],
                    'location_features' => [
                        'Seaside' => [
                            'type' => 'boolean'
                        ],
                        'Urban' => [
                            'type' => 'boolean'
                        ],
                        'Suburban' => [
                            'type' => 'boolean'
                        ],
                        'Rural' => [
                            'type' => 'boolean'
                        ],
                        'GatedCommunity' => [
                            'type' => 'boolean'
                        ],
                        'QuietNeighborhood' => [
                            'type' => 'boolean'
                        ],
                    ],
                ],
            ],
        ];
    }
}
