<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\CountryData\Country;
use App\Models\CountryData\State;
use App\Models\Scholarship\Scholarship;
use App\Models\User;
use Auth;
use Illuminate\Http\Request;

class DataController extends Controller
{
    public function getStatesByCountry($country_id)
    {
        return State::where('country_id', $country_id)->get(['id', 'name']);
    }

    public function getCountries()
    {
        return Country::selectRaw("id, CONCAT(UPPER(SUBSTRING(name, 1, 1)), LOWER(SUBSTRING(name, 2))) as name")
            ->get()
            ->pluck('name', 'id')
            ->toArray();
    }

    public function userdata()
    {
        $user = Auth::user();
        // $user = User::where('id', $user->id)->with('student')->first();
        $user = User::where('id', $user->id)->with(['student', 'student.educationDetails', 'student.employmentDetails', 'student.guardianDetails', 'student.addressDetails', 'student.documents'])->first();

        $scholarships = Scholarship::all();
        dd($user);
    }
}
