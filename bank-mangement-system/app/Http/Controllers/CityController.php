<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\City;

class CityController extends Controller
{
	public function getCity( Request $request )
	{
		return City::where('state_id', $request->input('stateId'))->pluck('name', 'id');
	}
}
