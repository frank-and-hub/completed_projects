<?php

namespace Database\Seeders;

use App\Models\DemoData;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoPropertySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $demoPropertyData = [
            [
                'title' => 'Modern 2-Bedroom Apartment in the Heart of Sandton – Prime Location!',
                'address' => [
                    "streetName" => "Sandton",
                    "unitNumber" => null,
                    "complexName" => null,
                    "streetNumber" => "12"
                ],
                'financial' => [
                    "levy" => null,
                    "price" => "14500",
                    "currency" => "ZAR",
                    "isReduced" => 0,
                    "leasePeriod" => "2",
                    "ratesAndTaxes" => null,
                    "currency_symbol" => "R",
                    "depositRequired" => "28000"
                ],
                'propertyType' => 'Apartment',
                'propertyStatus' => 'Rental Monthly',
                'country' => 'South Africa',
                'province' => 'Gauteng',
                'town' => 'Sandton',
                'suburb' => 'River Club & Ext',
                'showOnMap' => false,
                'bedrooms' => '2',
                'bathrooms' => '2',
                'location_views' => [
                    "ParkView",
                    "GardenView"
                ],
                'connectivity' => [
                    "WiFiReady",
                ],
                'outdoor_areas' => [
                    "privateGarden",
                    "Balcony"
                ],
                'parking' => [
                    "Garage",
                    "UndergroundParking",
                    "SecureParking",
                    "DisabledParking"
                ],
                'security_features' => [
                    "AlarmSystem",
                    "CCTV",
                    "BiometricAccess",
                    "IntercomSystem"
                ],
                'energy_efficiency' => [
                    "SolarPanels",
                    "PrepaidElectricity",
                    "GasGeyser"
                ],
                'furnishing' => [
                    "FullyFurnished"
                ],
                'kitchen_features' => [
                    "OpenPlanKitchen",
                    "BuiltInAppliances",
                    "GraniteCounterTops",
                    "InductionStove"
                ],
                'cooling_heating' => [
                    "AirConditioning",
                    "CentralHeating"
                ],
                'laundry_facilities' => [
                    "InUnitWasher",
                    "LaundryService"
                ],
                'technology' => [
                    "SmartHomeSystem",
                    "SmartLocks"
                ],
                'pet_policy' => [
                    "PetFriendly",
                    "LargePetsAllowed"
                ],
                'leisure_amenities' => [
                    "SwimmingPool",
                    "Jacuzzi",
                    "Sauna",
                    "SteamRoom",
                    "GolfCourse"
                ],
                'building_features' => [
                    "Elevator",
                    "Gym",
                    "Concierge",
                    "OnsiteManagement",
                    "Playground",
                    "SharedCourtyard"
                ],
                'flooring' => [
                    "TiledFloors"
                ],
                'proximity' => [
                    "NearPublicTransport",
                    "NearShoppingCenters",
                    "NearParks",
                    "NearCoffeeShops"
                ],
                'storage_space' => [
                    "NearPublicTransport"
                ],
                'communal_areas' => [
                    "ClubHouse",
                    "SharedLounge",
                    "BusinessCenter",
                    "MeetingRooms"
                ],
                'maintenance_services' => [
                    "GardenMaintenance",
                    "PoolMaintenance",
                    "CleaningServices",
                    "LaundryService"
                ],
                'water_features' => [
                    "RainwaterHarvesting",
                    "WaterTanks"
                ],
                'entertainment' => [
                    "CinemaRoom",
                    "Bar",
                    "GamesRoom"
                ],
                'accessibility' => [
                    "WheelchairAccess"
                ],
                'lease_options' => [
                    "LongTermLease"
                ],
                'location_features' => [],
                'noise_control_features' => [
                    "SoundProofing"
                ],
                'fire_safety_features' => [
                    "SmokeDetectors",
                    "FireExitPlan"
                ],
                'description' => 'Looking for the perfect lock-up-and-go in Sandton? This stylish 2-bedroom, 2-bathroom
                                    apartment is located in a secure, upmarket complex, just minutes from Sandton City and the
                                    Gautrain.

                                    - Spacious open-plan living area with sliding doors leading to a private balcony offering great
                                    city views
                                    - Fully fitted kitchen with stone countertops, ample cupboard space & space for all appliances
                                    - Main bedroom with en-suite bathroom plus a second full bathroom
                                    - 24-hour security, access control, pool, and gym


                                    Unbeatable location! Walk to top restaurants, business hubs, and entertainment spots. Ideal for
                                    professionals looking for convenience and style!',
                'lat' => -26.088082633165413,
                'lng' => 28.04664846726269,
                'action' => 'add',
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
                'images' => [
                    asset('assets/admin/demo/Sandton/freepik__the-style-is-candid-image-photography-with-natural__95525.jpeg'),
                    asset('assets/admin/demo/Sandton/freepik__the-style-is-candid-image-photography-with-natural__95526.jpeg'),
                    asset('assets/admin/demo/Sandton/freepik__the-style-is-candid-image-photography-with-natural__95527.jpeg'),
                    asset('assets/admin/demo/Sandton/freepik__the-style-is-candid-image-photography-with-natural__95527.jpeg'),
                    asset('assets/admin/demo/Sandton/freepik__the-style-is-candid-image-photography-with-natural__95529.jpeg'),
                    asset('assets/admin/demo/Sandton/freepik__the-style-is-candid-image-photography-with-natural__95530.jpeg'),
                ],
                'description_json' => ['description'],
            ],
            [
                'title' => 'Stunning 2-Bedroom Beachfront Apartment in Sea Point – Breathtaking Views!',
                'address' => [
                    "streetName" => "Sea Point",
                    "unitNumber" => null,
                    "complexName" => null,
                    "streetNumber" => "Sea Point"
                ],
                'financial' => [
                    "levy" => null,
                    "price" => "22000",
                    "currency" => "ZAR",
                    "isReduced" => 0,
                    "leasePeriod" => "2",
                    "ratesAndTaxes" => null,
                    "currency_symbol" => "R",
                    "depositRequired" => "44000"
                ],
                'propertyType' => 'House',
                'propertyStatus' => 'Rental Monthly',
                'country' => 'South Africa',
                'province' => 'Western Cape',
                'town' => 'Cape Town',
                'suburb' => 'Diep River',
                'showOnMap' => false,
                'bedrooms' => '2',
                'bathrooms' => '2',
                'location_views' => [],
                'connectivity' => [
                    "fiber",
                    "WiFiReady"
                ],
                'outdoor_areas' => [],
                'parking' => [
                    "Garage"
                ],
                'security_features' => [
                    "24/7Security",
                    "SecurityGates",
                    "AlarmSystem",
                    "CCTV"
                ],
                'energy_efficiency' => [],
                'furnishing' => [
                    "FullyFurnished"
                ],
                'kitchen_features' => [
                    "PrepaidElectricity",
                    "SeparateKitchen",
                    "FullyEquippedKitchen",
                    "GraniteCounterTops",
                    "InductionStove"
                ],
                'cooling_heating' => [
                    "AirConditioning",
                    "CentralHeating"
                ],
                'laundry_facilities' => [
                    "InUnitDryer",
                    "WashingLine"
                ],
                'technology' => [
                    "SmartHomeSystem",
                    "SmartLocks",
                    "HomeAutomation"
                ],
                'pet_policy' => [
                    "NoPetsAllowed"
                ],
                'leisure_amenities' => [
                    "SwimmingPool",
                    "OutdoorGym"
                ],
                'building_features' => [],
                'flooring' => [],
                'proximity' => [],
                'storage_space' => [],
                'communal_areas' => [],
                'maintenance_services' => [],
                'water_features' => [],
                'entertainment' => [],
                'accessibility' => [
                    "EntertainmentArea"
                ],
                'lease_options' => [],
                'location_features' => [],
                'noise_control_features' => [
                    "SoundProofing"
                ],
                'fire_safety_features' => [
                    "SprinklerSystem",
                    "FireExitPlan"
                ],
                'description' => 'Step into coastal luxury with this beautifully designed 2-bedroom, 2-bathroom apartment right on
                    the Sea Point Promenade. Perfect for those who love the ocean lifestyle!

                    -Light-filled open-plan lounge & dining area with floor-to-ceiling windows offering
                    panoramic sea views
                    - Modern kitchen with high-end finishes, built-in oven & hob
                    - Spacious bedrooms with built-in cupboards, main bedroom en-suite
                    - Secure complex with 24-hour security, concierge, pool, and underground parking


                    Walk to the beach, cafes, and top restaurants. Close to Clifton, Camps Bay & V&A
                    Waterfront.',
                'lat' => -26.088082633165413,
                'lng' => 28.04664846726269,
                'action' => 'add',
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
                'images' => [
                    asset('assets/admin/demo/Sea_Point/freepik__the-style-is-candid-image-photography-with-natural__95531.jpeg'),
                    asset('assets/admin/demo/Sea_Point/freepik__the-style-is-candid-image-photography-with-natural__95532.jpeg'),
                    asset('assets/admin/demo/Sea_Point/freepik__the-style-is-candid-image-photography-with-natural__95533.jpeg'),
                    asset('assets/admin/demo/Sea_Point/freepik__the-style-is-candid-image-photography-with-natural__95534.jpeg'),
                    asset('assets/admin/demo/Sea_Point/freepik__the-style-is-candid-image-photography-with-natural__95535.jpeg'),
                ],
                'description_json' => ['description'],
            ],
            [
                'title' => '3-Bedroom Home in Secure Estate – Umhlanga Ridge',
                'address' => [
                    "streetName" => "Ingwavuma",
                    "unitNumber" => null,
                    "complexName" => null,
                    "streetNumber" => "Ingwavuma"
                ],
                'financial' => [
                    "levy" => null,
                    "price" => "18500",
                    "currency" => "ZAR",
                    "isReduced" => 0,
                    "leasePeriod" => "2",
                    "ratesAndTaxes" => null,
                    "currency_symbol" => "R",
                    "depositRequired" => "48000"
                ],
                'propertyType' => 'House',
                'propertyStatus' => 'Rental Monthly',
                'country' => 'South Africa',
                'province' => 'Kwazulu Natal',
                'town' => 'Ingwavuma',
                'suburb' => 'Ingwavuma',
                'showOnMap' => false,
                'bedrooms' => '3',
                'bathrooms' => '2',
                'location_views' => [],
                'connectivity' => [
                    "WiFiReady",
                    "5gCoverage"
                ],
                'outdoor_areas' => [],
                'parking' => [
                    "CoveredParking"
                ],
                'security_features' => [
                    "SecurityGates",
                    "CCTV",
                    "IntercomSystem",
                    "AccessControl"
                ],
                'energy_efficiency' => [],
                'furnishing' => [],
                'kitchen_features' => [
                    "FullyEquippedKitchen",
                    "GraniteCounterTops",
                    "InductionStove"
                ],
                'cooling_heating' => [
                    "AirConditioning",
                    "CentralHeating"
                ],
                'laundry_facilities' => [
                    "InUnitWasher"
                ],
                'technology' => [
                    "SmartHomeSystem",
                    "SmartLighting",
                    "SmartLocks",
                    "HomeAutomation"
                ],
                'pet_policy' => [],
                'leisure_amenities' => [
                    "SwimmingPool",
                    "TennisCourt",
                    "OutdoorGym"
                ],
                'building_features' => [],
                'flooring' => [],
                'proximity' => [],
                'storage_space' => [],
                'communal_areas' => [],
                'maintenance_services' => [],
                'water_features' => [],
                'entertainment' => [
                    "CinemaRoom"
                ],
                'accessibility' => [
                    "WheelchairAccess",
                    "WideDoorways",
                    "GamesRoom"
                ],
                'lease_options' => [],
                'location_features' => [],
                'noise_control_features' => [
                    "SoundProofing",
                    "DoubleGlazedWindows"
                ],
                'fire_safety_features' => [
                    "SprinklerSystem",
                    "SmokeDetectors",
                    "FireExitPlan"
                ],
                'description' => 'This gorgeous 3-bedroom, 2.5-bathroom home in the sought-after Izinga Ridge Estate is perfect
                        for families or professionals looking for space, security, and convenience.

                        - Bright and airy open-plan living areas leading onto a private garden & entertainment patio
                        - Stylish kitchen with granite countertops, separate scullery, and ample cupboard space
                        - Main bedroom with walk-in closet & en-suite bathroom
                        - 24-hour security, clubhouse, walking trails, and play areas for kids
                        - 5 mins from Gateway Mall, Umhlanga Beach, and top schools. Easy access to M4 & N2.',
                'lat' => -26.088082633165413,
                'lng' => 28.04664846726269,
                'action' => 'add',
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
                'images' => [
                    asset('assets/admin/demo/Umlanga/freepik__the-style-is-candid-image-photography-with-natural__66958.jpeg'),
                    asset('assets/admin/demo/Umlanga/freepik__the-style-is-candid-image-photography-with-natural__66959.jpeg'),
                    asset('assets/admin/demo/Umlanga/freepik__the-style-is-candid-image-photography-with-natural__66960.jpeg'),
                    asset('assets/admin/demo/Umlanga/freepik__the-style-is-candid-image-photography-with-natural__66961.jpeg'),
                    asset('assets/admin/demo/Umlanga/freepik__the-style-is-candid-image-photography-with-natural__67893.jpeg'),
                ],
                'description_json' => ['description'],
            ],
            [
                'title' => 'Spacious 4-Bedroom Family Home in White River – Secure Estate Living!',
                'address' => [
                    "streetName" => "White River",
                    "unitNumber" => null,
                    "complexName" => null,
                    "streetNumber" => "White River"
                ],
                'financial' => [
                    "levy" => null,
                    "price" => "16000",
                    "currency" => "ZAR",
                    "isReduced" => 0,
                    "leasePeriod" => "2",
                    "ratesAndTaxes" => null,
                    "currency_symbol" => "R",
                    "depositRequired" => "26000"
                ],
                'propertyType' => 'Townhouse',
                'propertyStatus' => 'Rental Monthly',
                'country' => 'South Africa',
                'province' => 'Mpumalanga',
                'town' => 'White River',
                'suburb' => 'White River Ext 9',
                'showOnMap' => false,
                'bedrooms' => '4',
                'bathrooms' => '3',
                'location_views' => [
                    "ParkView",
                    "LakeView"
                ],
                'connectivity' => [
                    "fiber",
                    "WiFiReady"
                ],
                'outdoor_areas' => [
                    "privateGarden",
                    "Balcony"
                ],
                'parking' => [
                    "Garage"
                ],
                'security_features' => [
                    "24/7Security",
                    "CCTV",
                    "BiometricAccess"
                ],
                'energy_efficiency' => [
                    "SolarPanels"
                ],
                'furnishing' => [
                    "Furnishing"
                ],
                'kitchen_features' => [
                    "OpenPlanKitchen",
                    "PrepaidElectricity",
                    "SeparateKitchen",
                    "FullyEquippedKitchen",
                    "GraniteCounterTops"
                ],
                'cooling_heating' => [
                    "AirConditioning",
                    "UnderfloorHeating"
                ],
                'laundry_facilities' => [
                    "WashingLine",
                    "LaundryService"
                ],
                'technology' => [
                    "SmartHomeSystem",
                    "SmartThermosta",
                    "SmartLighting",
                    "SmartLocks"
                ],
                'pet_policy' => [],
                'leisure_amenities' => [
                    "SwimmingPool",
                    "Sauna",
                    "BasketballCourt",
                    "GolfCourse"
                ],
                'building_features' => [
                    "Elevator",
                    "Gym"
                ],
                'flooring' => [],
                'proximity' => [
                    "NearPublicTransport",
                    "NearSchools",
                    "NearShoppingCenters",
                    "NearParks"
                ],
                'storage_space' => [
                    "NearPublicTransport",
                    "BasementStorage",
                    "AtticStorage"
                ],
                'communal_areas' => [],
                'maintenance_services' => [],
                'water_features' => [],
                'entertainment' => [
                    "CinemaRoom",
                    "PrivateTheatre"
                ],
                'accessibility' => [
                    "WheelchairAccess",
                    "EntertainmentArea",
                    "GamesRoom"
                ],
                'lease_options' => [],
                'location_features' => [],
                'noise_control_features' => [
                    "SoundProofing",
                    "DoubleGlazedWindows"
                ],
                'fire_safety_features' => [
                    "SprinklerSystem",
                    "SmokeDetectors"
                ],
                'description' => 'If you’re looking for a peaceful lifestyle close to nature, this stunning 4-bedroom, 3-bathroom
                        home in a secure estate is perfect for you!

                        - Generous open-plan living areas with a fireplace – ideal for cozy family evenings
                        - Large covered patio & braai area overlooking a beautiful garden
                        - Modern kitchen with ample storage & high-end finishes
                        - Pet-friendly & perfect for families!

                        Just 20 minutes from Nelspruit and close to Kruger National Park, excellent schools, and
                        shopping centres.',
                'lat' => -26.088082633165413,
                'lng' => 28.04664846726269,
                'action' => 'add',
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
                'images' => [
                    asset('assets/admin/demo/White_River/freepik__the-style-is-candid-image-photography-with-natural__67883.jpeg'),
                    asset('assets/admin/demo/White_River/freepik__the-style-is-candid-image-photography-with-natural__67884.jpeg'),
                    asset('assets/admin/demo/White_River/freepik__the-style-is-candid-image-photography-with-natural__67885.jpeg'),
                    asset('assets/admin/demo/White_River/freepik__the-style-is-candid-image-photography-with-natural__67886.jpeg'),
                    asset('assets/admin/demo/White_River/freepik__the-style-is-candid-image-photography-with-natural__67887.jpeg'),
                ],
                'description_json' => ['description'],
            ],
            [
                'title' => 'Beachside Living in Summerstrand – 2-Bedroom Apartment Walking Distance  to the Ocean!',
                'address' => [
                    "streetName" => "Eastern cape",
                    "unitNumber" => null,
                    "complexName" => null,
                    "streetNumber" => "Eastern Cape"
                ],
                'financial' => [
                    "levy" => null,
                    "price" => "10500",
                    "currency" => "ZAR",
                    "isReduced" => 0,
                    "leasePeriod" => "2",
                    "ratesAndTaxes" => null,
                    "currency_symbol" => "R",
                    "depositRequired" => "25000"
                ],
                'propertyType' => 'Apartment',
                'propertyStatus' => 'Rental Monthly',
                'country' => 'South Africa',
                'province' => 'Eastern Cape',
                'town' => 'Port Elizabeth',
                'suburb' => 'Summerstrand',
                'showOnMap' => false,
                'bedrooms' => '2',
                'bathrooms' => '2',
                'location_views' => [
                    "SeaView"
                ],
                'connectivity' => [
                    "fiber",
                ],
                'outdoor_areas' => [
                    "privateGarden",
                ],
                'parking' => [
                    "Garage",
                    "StreetParking"
                ],
                'security_features' => [
                    "24/7Security",
                    "SecurityGates",
                    "ElectricFence",
                    "AlarmSystem",
                    "CCTV"
                ],
                'energy_efficiency' => [],
                'furnishing' => [
                    "FullyFurnished"
                ],
                'kitchen_features' => [
                    "OpenPlanKitchen",
                    "PrepaidElectricity",
                    "Pantry",
                    "GraniteCounterTops",
                    "DoubleOven"
                ],
                'cooling_heating' => [
                    "AirConditioning",
                    "CeilingFans",
                    "UnderfloorHeating"
                ],
                'laundry_facilities' => [
                    "InUnitWasher",
                    "InUnitDryer"
                ],
                'technology' => [
                    "SmartHomeSystem",
                    "SmartThermosta",
                    "SmartLighting"
                ],
                'pet_policy' => [
                    "PetFriendly"
                ],
                'leisure_amenities' => [
                    "SwimmingPool",
                    "TennisCourt",
                    "SquashCourt",
                    "OutdoorGym"
                ],
                'building_features' => [
                    "Elevator",
                    "Gym"
                ],
                'flooring' => [
                    "TiledFloors"
                ],
                'proximity' => [],
                'storage_space' => [
                    "StorageRoom",
                    "GardenShed"
                ],
                'communal_areas' => [],
                'maintenance_services' => [
                    "CleaningServices"
                ],
                'water_features' => [],
                'entertainment' => [
                    "PrivateTheatre",
                    "Bar",
                    "GamesRoom"
                ],
                'accessibility' => [],
                'lease_options' => [
                    "ShortTermLease",
                    "MonthToMonthLease"
                ],
                'location_features' => [],
                'noise_control_features' => [
                    "SoundProofing"
                ],
                'fire_safety_features' => [],
                'description' => 'Live just 300m from the beach in this stylish 2-bedroom, 2-bathroom apartment! Whether you’re
                    a young professional, student, or retiree, this home offers a relaxed, coastal lifestyle.

                    - Open-plan living area leading to a private balcony
                    - Fully fitted kitchen with built-in oven & hob
                    - Secure complex with access control, swimming pool & braai area
                    - Close to NMU, Boardwalk Mall & beachfront restaurants',
                'lat' => -26.088082633165413,
                'lng' => 28.04664846726269,
                'action' => 'add',
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
                'images' => [
                    asset('assets/admin/demo/Summerstrand/freepik__the-style-is-candid-image-photography-with-natural__67888.jpeg'),
                    asset('assets/admin/demo/Summerstrand/freepik__the-style-is-candid-image-photography-with-natural__67889.jpeg'),
                    asset('assets/admin/demo/Summerstrand/freepik__the-style-is-candid-image-photography-with-natural__67890.jpeg'),
                    asset('assets/admin/demo/Summerstrand/freepik__the-style-is-candid-image-photography-with-natural__67891.png'),
                ],
                'description_json' => ['description'],
            ],
            [
                'title' => 'Stylish 2-Bedroom Apartment in Green Point – Urban Living at Its Best',
                'address' => [
                    "streetName" => "Eastern cape",
                    "unitNumber" => null,
                    "complexName" => null,
                    "streetNumber" => "Green Point 3rd Avenue"
                ],
                'financial' => [
                    "levy" => null,
                    "price" => "18000",
                    "currency" => "ZAR",
                    "isReduced" => 0,
                    "leasePeriod" => "2",
                    "ratesAndTaxes" => null,
                    "currency_symbol" => "R",
                    "depositRequired" => "20000"
                ],
                'propertyType' => 'Apartment',
                'propertyStatus' => 'Rental Monthly',
                'country' => 'South Africa',
                'province' => 'Western Cape',
                'town' => 'Cape Town',
                'suburb' => 'Green Point',
                'showOnMap' => false,
                'bedrooms' => '2',
                'bathrooms' => '2',
                'location_views' => [],
                'connectivity' => [
                    "fiber",
                    "WiFiReady"
                ],
                'outdoor_areas' => [],
                'parking' => [
                    "Garage"
                ],
                'security_features' => [
                    "24/7Security",
                    "SecurityGates",
                    "AlarmSystem",
                    "CCTV"
                ],
                'energy_efficiency' => [],
                'furnishing' => [
                    "FullyFurnished"
                ],
                'kitchen_features' => [
                    "PrepaidElectricity",
                    "SeparateKitchen",
                    "FullyEquippedKitchen",
                    "GraniteCounterTops",
                    "InductionStove"
                ],
                'cooling_heating' => [
                    "AirConditioning",
                    "CentralHeating"
                ],
                'laundry_facilities' => [
                    "InUnitDryer",
                    "WashingLine"
                ],
                'technology' => [
                    "SmartHomeSystem",
                    "SmartLocks",
                    "HomeAutomation"
                ],
                'pet_policy' => [
                    "NoPetsAllowed"
                ],
                'leisure_amenities' => [
                    "SwimmingPool",
                    "OutdoorGym"
                ],
                'building_features' => [],
                'flooring' => [],
                'proximity' => [],
                'storage_space' => [],
                'communal_areas' => [],
                'maintenance_services' => [],
                'water_features' => [],
                'entertainment' => [],
                'accessibility' => [
                    "EntertainmentArea"
                ],
                'lease_options' => [],
                'location_features' => [],
                'noise_control_features' => [
                    "SoundProofing"
                ],
                'fire_safety_features' => [
                    "SprinklerSystem",
                    "FireExitPlan"
                ],
                'description' => 'Discover the perfect blend of city life and coastal charm in this modern 2-bedroom, 2-bathroom
                        apartment in Green Point. Ideal for young professionals and couples who love vibrant city living
                        with a touch of ocean breeze.
                        ✔ Open-plan lounge & dining area with large windows offering stunning views of the city and
                        mountain
                        ✔ Contemporary kitchen with sleek finishes, built-in oven & hob, plus space for all appliances
                        ✔ Spacious bedrooms with built-in cupboards, main bedroom with en-suite bathroom
                        ✔ Secure building with 24-hour security, access control, pool, and gym facilities
                        📍 Just a short stroll to the V&A Waterfront, cafes, bars, and the famous Green Point Park.
                        Quick access to the city center and the beach. Perfect location for a work-play lifestyle! ',
                'lat' => -26.088082633165413,
                'lng' => 28.04664846726269,
                'action' => 'add',
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
                'images' => [
                    asset('assets/admin/demo/Sandton/freepik__the-style-is-candid-image-photography-with-natural__95526.jpeg'),
                    asset('assets/admin/demo/Sea_Point/freepik__the-style-is-candid-image-photography-with-natural__95532.jpeg'),
                    asset('assets/admin/demo/Umlanga/freepik__the-style-is-candid-image-photography-with-natural__66959.jpeg'),
                    asset('assets/admin/demo/White_River/freepik__the-style-is-candid-image-photography-with-natural__67884.jpeg'),
                    asset('assets/admin/demo/Summerstrand/freepik__the-style-is-candid-image-photography-with-natural__67889.jpeg'),
                ],
                'description_json' => ['description'],
            ],
        ];
        try {
            DB::beginTransaction();
            $insert = [];
            foreach ($demoPropertyData as $key => $value) {
                foreach ($value as $k => $v) {
                    if (is_array($v)) {
                        $insert[$key][$k] = count($v) != 0 ? json_encode($v) : null;
                    } else {
                        $insert[$key][$k] = $v;
                    }
                }
                DemoData::updateOrCreate(['title' => $value['title']], $insert[$key]);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
