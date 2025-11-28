<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\InternalProperty>
 */
class InternalPropertyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'lat'               => $this->faker->latitude(6.5, 37),
            'lng'               => $this->faker->latitude(68, 97),
            'admin_id'          => '9d565b8d-8d39-41ae-9c08-0dc0ab40420b',
            'is_agency'         => 1,
            'title'             => $this->faker->name(),
            'financials'        => [
                "levy"              => $this->faker->numberBetween(1, 50),
                "price"             => $this->faker->numberBetween(1000, 100000),
                "currency"          => "ZAR",
                "isReduced"         => 0,
                "leasePeriod"       => $this->faker->numberBetween(6, 36),
                "ratesAndTaxes"     => $this->faker->numberBetween(11, 100),
                "currency_symbol"   => "R",
                "depositRequired"   => $this->faker->numberBetween(100, 1000)
            ],
            'landSize'          => json_encode($this->faker->numberBetween(1, 1000)),
            'buildingSize'      => json_encode($this->faker->numberBetween(1, 1000)),
            'propertyType'      => 'Apartment',
            'propertyStatus'    => 'Rental Monthly',
            'country'           => 'South Afria',
            'province'          => 'Western cape',
            'town'              => 'Cape town',
            'suburb'            => 'See point',
            'address'           => $this->faker->sentence(),
            'bedrooms'          => $this->faker->numberBetween(1, 10),
            'bathrooms'         => $this->faker->numberBetween(1, 100),
            'description'       => $this->faker->sentence()

        ];
    }
}
