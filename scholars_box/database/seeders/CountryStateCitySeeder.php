<?php

namespace Database\Seeders;

use App\DataProviders\CityDataProvider;
use App\DataProviders\CountryDataProvider;
use App\DataProviders\StateDataProvider;
use App\Models\CountryData\City;
use App\Models\CountryData\Country;
use App\Models\CountryData\State;
use Illuminate\Database\Seeder;

class CountryStateCitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Country::insertOrIgnore(CountryDataProvider::data());
        State::insertOrIgnore(StateDataProvider::data());
    }
}
