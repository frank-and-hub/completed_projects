<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\AdminSubscription;
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
        $totalUser = User::count();
        $totalRequest = Contactus::count();
        $totalsearchproperty = UserSearchProperty::count();

        $plans = Plans::withCount('user_search_property')
            ->whereIn('plan_name', ['Basic', 'Professional'])
            ->get();

        $basicPlanProperty = $plans->firstWhere('plan_name', 'Basic')->user_search_property_count ?? 0;
        $professionalPlanProperty = $plans->firstWhere('plan_name', 'Professional')->user_search_property_count ?? 0;

        // Get total revenue according to plan
        $basicRevenue = UserSubscription::whereHas('plan', function ($query) {
            $query->where('plan_name', 'Basic');
        })->sum('amount');

        $professionalRevenue = UserSubscription::whereHas('plan', function ($query) {
            $query->where('plan_name', 'Professional');
        })->sum('amount');

        $agencyRevenue = AdminSubscription::with([
            'plan',
            'admin'
        ])
            ->whereHas('plan')->whereHas('admin', function ($query) {
                $query->where(function ($query) {
                    $query->whereHas('roles', function ($roleQuery) {
                        $roleQuery->whereIn('name', ['agency']);
                    });
                });
            })
            ->sum('amount');

        $privateLandLordRevenue = AdminSubscription::with([
            'plan',
            'admin'
        ])
            ->whereHas('plan')->whereHas('admin', function ($query) {
                $query->where(function ($query) {
                    $query->whereHas('roles', function ($roleQuery) {
                        $roleQuery->whereIn('name', ['privatelandlord']);
                    });
                });
            })
            ->sum('amount');

        $totalRevenue = $basicRevenue + $professionalRevenue;
        $revenue = [
            'basic' => round($basicRevenue),
            'professional' => round($professionalRevenue),
            'total' => round($totalRevenue),
            'agency' => round($agencyRevenue),
            'privateLandLord' => round($privateLandLordRevenue)
        ];
        $years = UserSubscription::selectRaw('YEAR(created_at) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        $months = [];

        for ($i = 1; $i <= 12; $i++) {
            $months[] = [
                'value' => $i,
                'name' => date('F', mktime(0, 0, 0, $i, 1))
            ];
        }

        $totalProperty = Property::where('propertyStatus', '!=', 'Inactive')->count();
        $totalPropertyClient = PropertyClientOffice::count();
        $data = [
            'totaluser' => $totalUser,
            'totalRequest' => $totalRequest,
            'totalsearchproperty' => $totalsearchproperty,
            'basicPlanProperty' => $basicPlanProperty,
            'professionalPlanProperty' => $professionalPlanProperty,
            'revenue' => $revenue,
            'years' => $years,
            'months' => $months,
            'totalProperty' => $totalProperty,
            'totalPropertyClient' => $totalPropertyClient,
            'totalInternalProperty' => InternalProperty::count()
        ];
        return view('home', compact('active_page', 'data', 'title'));
    }

    public function map()
    {
        $active_page = 'Properties location';
        $title = 'Properties Location';
        return view('map', compact('active_page',  'title'));
    }

    public function total_revenue(Request $request)
    {
        $year = $request->input('year');
        $month = $request->input('month');

        $userCount = UserSubscription::count();
        $adminCount = AdminSubscription::count();

        if ($userCount == 0 && $adminCount == 0) {
            return response()->json(['message' => 'No Data Found', 'status' => 404]);
        }

        // Retrieve UserSubscriptions with related Plan
        $userSubscriptions = UserSubscription::selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, WEEK(created_at) as week, subscription_id, SUM(amount) as total')
            ->whereYear('created_at', $year)
            ->with('plan:id,plan_name,type')
            ->whereHas('plan', function ($query) {
                $query->where('type', 'tenant');
            })
            ->when($month, fn($query) => $query->whereMonth('created_at', $month))
            ->groupBy('year', 'month', 'week', 'subscription_id')
            ->get();

        // Retrieve AdminSubscriptions with related Admin and Plan
        $adminSubscriptions = AdminSubscription::selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, WEEK(created_at) as week, subscription_id, SUM(amount) as total')
            ->whereYear('created_at', $year)
            ->with(['plan:id,plan_name,type'])
            ->whereHas('plan', function ($query) {
                $query->whereIn('type', ['agency', 'privatelandlord']);
            })
            ->when($month, fn($query) => $query->whereMonth('created_at', $month))
            ->groupBy('year', 'month', 'week', 'subscription_id')
            ->get();

        if ($userSubscriptions->isEmpty() && $adminSubscriptions->isEmpty()) {
            return response()->json(['message' => 'No Data Found']);
        }

        $query = $userSubscriptions->concat($adminSubscriptions);

        $data = [];
        if ($month) {
            $firstDayOfMonth = Carbon::createFromDate($year, $month, 1)->startOfMonth();

            $query->groupBy('week')
                ->each(function ($weekData, $week) use (&$data, $firstDayOfMonth) {
                    $weekOfMonth = $firstDayOfMonth->copy()->addWeeks($week + $firstDayOfMonth->weekOfMonth)->weekOfMonth;

                    $basicRevenue = $weekData->filter(function ($item) {
                        if ($item instanceof UserSubscription) {
                            return $item->plan->plan_name === 'Basic'  && $item->plan->type === 'tenant';
                        } else {
                            return false;
                        }
                    })->sum('total');

                    // Professional Plan revenue
                    $professionalRevenue = $weekData->filter(function ($item) {
                        if ($item instanceof UserSubscription) {
                            return $item->plan->plan_name === 'Professional'  && $item->plan->type === 'tenant';
                        } else {
                            return false;
                        }
                    })->sum('total');

                    // Agency Revenue
                    $agencyRevenue = $weekData->filter(function ($item) {
                        if ($item instanceof AdminSubscription) {
                            return $item->plan->type === 'agency' && $item->plan->plan_name === 'Basic';
                        } else {
                            return false;
                        }
                    })->sum('total');

                    // Private Landlord Revenue
                    $privateLandlordRevenue = $weekData->filter(function ($item) {
                        if ($item instanceof AdminSubscription) {
                            return $item->plan->type === 'privatelandlord' && $item->plan->plan_name === 'Basic';
                        } else {
                            return false;
                        }
                    })->sum('total');

                    // Push the data to the array
                    $data[] = [
                        'y' => 'Week ' . $weekOfMonth,
                        'a' => round($professionalRevenue),
                        'b' => round($basicRevenue),
                        'c' => round($agencyRevenue),
                        'd' => round($privateLandlordRevenue),
                    ];
                });
        } else {
            $query->groupBy('month')
                ->each(function ($monthData, $month) use (&$data) {
                    // Basic Plan revenue
                    $basicRevenue = $monthData->filter(function ($item) {
                        if ($item instanceof UserSubscription) {
                            return $item->plan->plan_name === 'Basic' && $item->plan->type === 'tenant';
                        } else {
                            return false;
                        }
                    })->sum('total');

                    // Professional Plan revenue
                    $professionalRevenue = $monthData->filter(function ($item) {
                        if ($item instanceof UserSubscription) {
                            return $item->plan->plan_name === 'Professional' && $item->plan->type === 'tenant';
                        } else {
                            return false;
                        }
                    })->sum('total');

                    // Agency Revenue
                    $agencyRevenue = $monthData->filter(function ($item) {
                        if ($item instanceof AdminSubscription) {
                            return $item->plan->type === 'agency' && $item->plan->plan_name === 'Basic';
                        } else {
                            return false;
                        }
                    })->sum('total');

                    // Private Landlord Revenue
                    $privateLandlordRevenue = $monthData->filter(function ($item) {
                        if ($item instanceof AdminSubscription) {
                            return $item->plan->type === 'privatelandlord' && $item->plan->plan_name === 'Basic';
                        } else {
                            return false;
                        }
                    })->sum('total');

                    // Push the data to the array
                    $data[] = [
                        'y' => Carbon::createFromDate(null, $month, 1)->format('F'),
                        'a' => round($professionalRevenue),
                        'b' => round($basicRevenue),
                        'c' => round($agencyRevenue),
                        'd' => round($privateLandlordRevenue),
                    ];
                });
        }

        return response()->json($data);
    }


    public function total_property(Request $request)
    {
        $year = $request->input('year');
        $month = $request->input('month');

        $count = UserSearchProperty::count();
        if ($count == 0) {
            return response()->json(['message' => 'No Data Found', 'status' => 404]);
        }
        $query = UserSearchProperty::selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, WEEK(created_at) as week, user_subscription_id, COUNT(*) as total')
            ->whereYear('created_at', $year)
            ->with('user_subscription');

        if ($month) {
            $query->whereMonth('created_at', $month);
        }

        $subscriptions = $query->groupBy('year', 'month', 'week', 'user_subscription_id')->get();
        if ($subscriptions->isEmpty()) {
            return response()->json(['message' => 'No Data Found']);
        }

        $data = [];
        if ($month) {
            $firstDayOfMonth = Carbon::createFromDate($year, $month, 1)->startOfMonth();
            $subscriptions->groupBy('week')->each(function ($weekData, $week) use (&$data, $firstDayOfMonth) {
                $weekOfMonth = $firstDayOfMonth->copy()->addWeeks($week + $firstDayOfMonth->weekOfMonth)->weekOfMonth;
                $basicProperty = $weekData->filter(function ($item) {
                    return $item->user_subscription->plan->plan_name === 'Basic';
                })->sum('total');
                $professionalProperty = $weekData->filter(function ($item) {
                    return $item->user_subscription->plan->plan_name === 'Professional';
                })->sum('total');
                $data[] = [
                    'y' => 'Week ' . $weekOfMonth,
                    'a' => $professionalProperty,
                    'b' => $basicProperty
                ];
            });
        } else {
            $subscriptions->groupBy('month')->each(function ($monthData, $month) use (&$data) {
                $basicProperty = $monthData->filter(function ($item) {
                    return $item->user_subscription->plan->plan_name === 'Basic';
                })->sum('total');
                $professionalProperty = $monthData->filter(function ($item) {
                    return $item->user_subscription->plan->plan_name === 'Professional';
                })->sum('total');
                $data[] = [
                    'y' => Carbon::createFromDate(null, $month, 1)->format('F'),
                    'a' => $professionalProperty,
                    'b' => $basicProperty
                ];
            });
        }

        return response()->json($data);
    }
}
