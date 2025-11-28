<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiPropertyResource;
use App\Http\Resources\PropertyClientOfficeResource;
use App\Models\PropertyClientOffice;
use App\Models\PropertyNeedsApiUser;
use App\Models\UserSearchProperty;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class apiSaleCotroller extends Controller
{
    public function officeslist()
    {
        $propertyOffice = PropertyClientOffice::get();
        $data = PropertyClientOfficeResource::collection($propertyOffice);
        return response()->json($data);
    }

    public function listings(Request $request)
    {
        if (!$request->getUser() || !$request->getPassword()) {
            return response('Authentication required', Response::HTTP_UNAUTHORIZED)
                ->header('WWW-Authenticate', 'Basic realm="My Realm"');
        } else {
            $checkApiUser = PropertyNeedsApiUser::where('user_name', $request->getUser())
                ->where('password', $request->getPassword())
                ->first();
            if ($checkApiUser) {
                $propertyRequest = UserSearchProperty::when($checkApiUser->country !== 'all', function ($q) use ($checkApiUser) {
                    $q->where('country', $checkApiUser->country);
                })->when($checkApiUser->city != '', function ($q) use ($checkApiUser) {
                    $q->where('city', $checkApiUser->city);
                })->when($checkApiUser->suburb_name != '', function ($q) use ($checkApiUser) {
                    $q->where('suburb_name', $checkApiUser->suburb_name);
                })->when($checkApiUser->property_type != '', function ($q) use ($checkApiUser) {
                    $q->where('property_type', $checkApiUser->property_type);
                })
                    ->get();
                $data = ApiPropertyResource::collection($propertyRequest);
                return response()->json($data);
            } else {
                return response('Invalid login credentials', Response::HTTP_UNAUTHORIZED)
                    ->header('WWW-Authenticate', 'Basic realm="My Realm"');
            }
        }
    }
}
