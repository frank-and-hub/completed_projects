<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\InternalProperty;
use App\Models\Plans;
use App\Models\User;
use App\Models\UserEmployment;
use App\Models\UserSearchProperty;
use App\Models\UserSubscription;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Arr;

class ReUseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->userDemoSeeder();
    }

    protected function userDemoSeeder()
    {
        $faker = Faker::create();
        $plans = Plans::whereType('tenant')->pluck('amount', 'id')->toArray();
        $planIds = array_keys($plans);
        $properties = InternalProperty::select('province', 'suburb', 'propertyType', 'bedrooms', 'bathrooms')->get();
        $country = Country::whereName('South Africa')->first();
        $propertyProvince = $properties->pluck('province')->unique()->values()->toArray();
        $propertySuburb = $properties->pluck('suburb')->unique()->values()->toArray();
        $propertyType = $properties->pluck('propertyType')->unique()->values()->toArray();
        $propertyBedrooms = $properties->pluck('bedrooms')->unique()->values()->toArray();
        $propertyBathrooms = $properties->pluck('bathrooms')->unique()->values()->toArray();

        try {
            DB::beginTransaction();
            for ($i = 0; $i < 100; $i++) {
                $now = Carbon::now();
                $planId = Arr::random($planIds);
                $phone = "11111" . rand(100000, 999999);
                $property_type = Arr::random($propertyType);
                $property_province = Arr::random($propertyProvince);
                $property_suburb = Arr::random($propertySuburb);
                $property_bedrooms = Arr::random($propertyBedrooms);
                $property_bathrooms = Arr::random($propertyBathrooms);

                $user =  User::create([
                    'name' => $faker->name,
                    'email' => $faker->email,
                    'country_code' => '+27',
                    'phone' => $phone,
                    'type' => 'user',
                    'password' => Hash::make('12345678'), // Use a secure password
                    'timeZone' => 'utc ',
                    'status' => 1,
                    'country' => $country->currency_name,
                    'email_verified_at' => $now,
                ]);

                $user->otpVerification()->updateOrCreate([
                    'phone' => $phone,
                ], [
                    'otp' => '',
                    'otp_generated_at' => $now,
                    'otp_verified_at' => $now,
                ]);

                UserEmployment::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'emplyee_type' => Arr::random([
                            'employed',
                            'contract',
                            'self_employed',
                            'student',
                            'retired',
                            'unemployed'
                        ]),
                        'live_with' => rand(1, 10), // Randomly assign a value between 1 and 10
                    ]
                );

                $userSubscriptionData = [
                    'user_id' => $user->id,
                    'subscription_id' => $planId,
                    'amount' => $plans[$planId],
                    'status' => 'ongoing',
                    'started_at' => $now,
                    'expired_at' => $now->copy()->addMonth()
                ];

                $userSubscription = UserSubscription::create($userSubscriptionData);

                $additionalFeatures = [
                    'pet_friendly' => rand(0, 1),
                    'parking' => rand(0, 1),
                    'pool' => rand(0, 1),
                    'fully_furnished' => rand(0, 1),
                    'garage' => rand(0, 1),
                    'garden' => rand(0, 1),
                    'move_in_date' => $now->copy()->addDay(),
                ];

                $userSearchPropertyData = [
                    'user_id' => $user->id,
                    'user_subscription_id' => $userSubscription->id,
                    'province_name' => $property_province,
                    'suburb_name' => $property_suburb,
                    'city' => $faker->city,
                    'country' => $country->currency_name,
                    'property_type' => $property_type,
                    'start_price' => rand(10, 100),
                    'end_price' => rand(2000, 10000),
                    'no_of_bedroom' => $property_bedrooms,
                    'no_of_bathroom' => $property_bathrooms,
                    'additional_features' => json_encode($additionalFeatures),
                    'currency' => $country->currency,
                    'currency_name' => $country->currency_name,
                    'currency_symbol' => $country->currency_symbol,
                ];

                UserSearchProperty::create($userSearchPropertyData);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            echo "Error occurred: " . $e->getMessage();
        }
    }
}
