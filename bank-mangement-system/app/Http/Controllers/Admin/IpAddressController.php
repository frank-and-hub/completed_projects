<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\IpAddresses;
use App\Models\Branch;

class IpAddressController extends Controller
{
    public function __construct()
    {

    }

    public function getIp( $branchId = null )
    {
    	$ipAddress = IpAddresses::where('user_id', Branch::find($branchId)->manager_id)->get();
	    return view('templates.admin.branch.branch-ip', ['title' => 'Branch Ips','branchIp' => $ipAddress]);
    }

    public function addIp( Request $request)
    {
	    $validator = Validator::make($request->all(), [
		    //'ip_address' => 'required|unique:ip_addresses|max:255',
		    'ip_address' => 'required|max:255',
	    ]);
	    if ($validator->fails()) {
		    return back()->with('alert', 'Ip Already Assigned!');
	    }
    	$ipAddress = new IpAddresses();
    	$ipAddress->user_id = $request->input('manager-id');
    	$ipAddress->ip_address = $request->input('ip_address');
    	$branchIp = $ipAddress->save();
    	$branchId = Branch::where('manager_id', $request->input('manager-id'))->pluck('id')->first();
    	//dd($branchId);
	    if ( $branchIp ) {
		    return back()->with('success', 'Ip Assign Successfully!');
	    } else {
		    return back()->with('alert', 'Problem With Assign Ip');
	    }
    }

    public function UpdateIp( Request $request )
    {
	    $messages = [
		    'required' => 'The :attribute field is required.',
		    'unique' => 'The :attribute field should be unique.',
		    'ip' => 'The :attribute field should be validate ip.',
	    ];
	    $validator = Validator::make($request->all(), [
		    //'ip_address' => 'required|unique:ip_addresses|max:255|ip',
		    'ip_address' => 'required|max:255|ip',
	    ], $messages);
	    if ($validator->fails()) {
		    return back()->with('alert', 'Something Wrong Ip Not Updated!');
	    }
    	$ipUpdate = IpAddresses::where('id', $request->input('id'))->update(['ip_address' => $request->input('ip_address')]);
	    if ( $ipUpdate ) {
		    return back()->with('success', 'Update Successfully!');
	    } else {
		    return back()->with('alert', 'Problem With Update Ip');
	    }
    }

    public function DestroyIp( $ipId = null )
    {
    	if( $ipId ) {
		    IpAddresses::find($ipId)->delete();
		    return back()->with('success', 'Ip Deleted Successfully!');
	    }
	    return back()->with('success', 'Problem With Delete Ip!');
    }
}
