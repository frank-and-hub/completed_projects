<?php

namespace App\Http\Controllers\adminsubuser;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Contactus;
use App\Models\InternalProperty;
use App\Models\Plans;
use App\Models\Property;
use App\Models\PropertyClientOffice;
use App\Models\User;
use App\Models\UserSearchProperty;
use App\Models\UserSubscription;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index()
    {
        $active_page = 'dashboard';
        $title = 'Dashboard';
        $admin = Auth::user();
        $role = $admin?->getRoleNames()->first() ?: '';
        $totalAgent = 0;
        $pvr = []; // Property Viewing Request
        $agentEvents = [];
        $postmanAPI = [];
        $acceptedPVR = 0;
        $totalPVR = 0;

        $today = Carbon::now()->toDateString();
        if ($role == 'agency') {
            $agents = Admin::where('admin_id', $admin->id)->get();
            $totalAgent = $agents->count();
            $totalProperties = 0;
            $totalMatchProperties = 0;
            foreach ($agents as $agent) {
                $totalProperties += $agent->property()->count();
                $totalMatchProperties += $agent->sendInternalPropertyUser()->count();
                $agentEvents = $agent->calendars()->with(['property:id,title', 'admin:id,name'])
                    ->where('status', 'accepted')
                    ->whereDate('event_datetime', $today)
                    ->get();
                $acceptedPVR = $agent->calendars()->with('property:id,title')->where('status', 'accepted')->count();
                $totalPVR = $agent->calendars()->with('property:id,title')->count();
            }
            $adminPlan = $admin->admin_subscription()
                ->where('status', 'ongoing')
                ->first();
            $postmanAPI = $admin->external_api()
                ->first();
        } else {
            $totalProperties = $admin->property()->count();
            $totalMatchProperties = $admin->sendInternalPropertyUser()->count();
            $agentEvents = $admin->calendars()
                ->with('property:id,title')
                ->where('status', 'accepted')
                ->whereDate('event_datetime', $today)
                ->get();
            $acceptedPVR = $admin->calendars()->with('property:id,title')->where('status', 'accepted')->count();
            $totalPVR = $admin->calendars()->with('property:id,title')->count();
            $adminPlan = $admin->admin_subscription()
                ->where('status', 'ongoing')
                ->first();
        }
        if ($role == 'agency' || $role == 'privatelandlord') {
            $totalPlan = $admin->admin_subscription()->count();
        }
        // return $agentEvents;
        $data = [
            'totalAgent' => $totalAgent,
            'totalProperty' => $totalProperties,
            'totalMatchProperties' => $totalMatchProperties,
            'agentEvents' => $agentEvents,
            'totalAppointments' => count($agentEvents),
            'postmanAPI' => $postmanAPI,
            'pvr' => [
                'acceptedPVR' => $acceptedPVR,
                'TotalPVR' => $totalPVR
            ],
            'admin' => $admin,
        ];

        if ($adminPlan) {
            $data['adminPlan'] = Carbon::parse($adminPlan->expired_at, 'Africa/Johannesburg')->format('d/m/y');
        }
        if (isset($totalPlan) && $totalPlan) {
            $data['totalPlan'] = $totalPlan;
        }
        return view('adminsubuser.home', compact('active_page', 'title', 'data'));
    }
}
