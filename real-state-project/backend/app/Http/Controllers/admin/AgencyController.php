<?php

namespace App\Http\Controllers\admin;

use App\Helpers\Helper;
use App\Helpers\ResponseBuilder;
use App\Helpers\WhatsappTemplate;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\AgencyRegister;
use App\Models\City;
use App\Models\Country;
use App\Models\Province;
use App\Models\State;
use App\Models\State_City;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AgencyController extends Controller
{
    public function create()
    {
        $active_page = 'agencies';
        $title = 'Agencies';
        $countries = ['india', 'usa'];
        $business_types = ['rent'];
        return view('adminsubuser.agency.create', compact('active_page', 'countries', 'business_types', 'title'));
    }

    public function store(Request $request)
    {
        try {
            //code...
            $request->merge([
                'contact' => str_replace(' ', '', $request->contact),
            ]);
            $request->merge([
                'contact' => str_replace('-', '', $request->contact),
            ]);
            $validator = Validator::make($request->all(), [
                'first_name' => 'required|max:150',
                'last_name' => 'required|max:150',
                'password' => 'required|max:100',
                'business_name' => 'required|max:250',
                'contact' => 'required|numeric|unique:' . Admin::class . ',phone',
                'dial_code' => 'required|numeric',
                'email' => 'required|email|unique:' . Admin::class . ',email',
                'owner_id' => 'required|max:150',
                'registration_number' => 'required|max:150',
                'vat_number' => 'required|max:150',
                'country' => 'required|exists:' . Country::class . ',id',
                'state' => 'required|exists:' . Province::class . ',id',
                'city' => 'required|exists:' . City::class . ',id',
                'street_address' => 'required|max:250',
                'street_address_2' => 'nullable|max:250',
                'postal' => 'required|max:150',
                'business_type' => 'required|in:rent',
                'description' => 'required|max:500',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            ]);

            if ($validator->fails()) {
                return ResponseBuilder::error($validator->errors()->first(), 200);
            }

            $fullName = $request->first_name . ' ' . $request->last_name;
            $path = null;
            $country = Country::findOrFail($request->country);
            DB::beginTransaction();
            $admin = Admin::create([
                'name' => $fullName,
                'email' => $request->email,
                'dial_code' => '+' . $request->dial_code,
                'password' => Hash::make(Helper::defaultPassword($request->password)),
                'phone' => $request->contact,
                'password_text' => $request->password,
                'request_type' => 'accepted',
                'country' => $country->name,
                'timeZone' => $country->timezones[0]['zoneName'],
            ]);

            if ($request->hasFile('image')) {
                $path = $this->__imageSave($request, 'image', 'agency_banner');
            }

            $admin
                ->agencyRegister()
                ->create([
                    'f_name' => $request->first_name,
                    'l_name' => $request->last_name,
                    'business_name' => $request->business_name,
                    'id_number' => $request->owner_id,
                    'registration_number' => $request->registration_number,
                    'vat_number' => $request->vat_number,
                    'street_address' => $request->street_address,
                    'street_address_2' => $request->street_address_2,
                    'postal_code' => $request->postal,
                    'type_of_business' => $request->business_type,
                    'message' => $request->description,
                    'agency_banner' => $path,
                    'country' => $request->country,
                    'province' => $request->state,
                    'city' => $request->city,
                ]);

            $admin->assignRole('agency');
            $credentialData = [
                'name' => $admin->name,
                'email' => $admin->email,
                'type' => 'agency',
                'password' => $admin->password_text,
            ];
            Helper::sendCredentialMail($credentialData);
            WhatsappTemplate::agencyWelcomeMessage($admin->dial_code, $admin->phone, $admin->name, $request->business_name, $admin->email, $admin->password_text);
            DB::commit();
            return ResponseBuilder::success(null, 'Successfully! Created Agenty.');
        } catch (\Throwable $th) {
            Log::error($th);
            DB::rollback();
            return ResponseBuilder::error($th->getMessage(), 500);
        }
    }

    public function edit(Admin $admin)
    {
        // return $admin;
        $agency_register = $admin->agencyRegister;
        $active_page = 'agencies';
        $title = 'Edit Agencies';
        $countries = ['india', 'usa'];
        $business_types = ['rent'];
        return view('adminsubuser.agency.edit', compact('active_page', 'admin', 'agency_register', 'countries', 'business_types', 'title'));
    }

    public function update(Request $request, Admin $admin)
    {
        try {
            //code...
            // return $request->all();
            // Remove spaces from the contact field
            $request->merge([
                'contact' => str_replace(' ', '', $request->contact),
            ]);
            $request->merge([
                'contact' => str_replace('-', '', $request->contact),
            ]);
            $validator = Validator::make($request->all(), [
                'first_name' => 'required|max:150',
                'last_name' => 'required|max:150',
                'business_name' => 'required|max:250',
                'contact' => 'required|numeric|unique:' . Admin::class . ',phone,' . $admin->id,
                'dial_code' => 'required|numeric',
                // 'email' => 'required|email|unique:'.Admin::class.',email,'.$admin->id,
                'owner_id' => 'required|max:150',
                'registration_number' => 'required|max:150',
                'vat_number' => 'required|max:150',
                'street_address' => 'required|max:250',
                'street_address_2' => 'nullable|max:250',
                // 'city'    => 'required|max:150',
                // 'state'    => 'required|max:150',
                'postal' => 'required|max:150',
                // 'country'    => 'required|max:150',
                'business_type' => 'required|in:rent',
                'description' => 'required|max:500',

                'country' => 'required|exists:' . Country::class . ',id',
                'state' => 'required|exists:' . State::class . ',id',
                'city' => 'required|exists:' . State_City::class . ',id',
            ]);

            if ($validator->fails()) {
                return ResponseBuilder::error($validator->errors()->first(), 200);
            }

            $fullName = $request->first_name . ' ' . $request->last_name;

            DB::beginTransaction();
            Admin::where('id', $admin->id)->update([
                'name' => $fullName,
                'email' => $request->email,
                'dial_code' => $request->dial_code,
                'phone' => $request->contact,
            ]);

            if ($request->password) {
                Admin::where('id', $admin->id)->update([
                    'password' => Hash::make(Helper::defaultPassword($request->password)),
                ]);
            }

            AgencyRegister::where('admin_id', $admin->id)->update([
                'f_name' => $request->first_name,
                'l_name' => $request->last_name,
                'business_name' => $request->business_name,
                'id_number' => $request->owner_id,
                'registration_number' => $request->registration_number,
                'vat_number' => $request->vat_number,
                'street_address' => $request->street_address,
                'street_address_2' => $request->street_address_2,
                'city' => $request->city,
                'province' => $request->state,
                'postal_code' => $request->postal,
                'type_of_business' => $request->business_type,
                'country' => $request->country,
                'message' => $request->description,
            ]);
            $admin->assignRole('agency');

            DB::commit();
            return ResponseBuilder::success(null, 'Successfully! Updated');
        } catch (\Throwable $th) {
            Log::error($th);
            DB::rollback();
            return ResponseBuilder::error($th->getMessage(), 500);
        }
    }
}
