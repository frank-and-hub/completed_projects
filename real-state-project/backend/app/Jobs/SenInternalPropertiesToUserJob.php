<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\UserSubscription;
use DateTime;
use DateTimeZone;
use Illuminate\Bus\Queueable;
use App\Helpers\Property as HelpersProperty;
use App\Helpers\WhatsappTemplate;
use App\Models\InternalProperty;
use App\Models\UserSearchProperty;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SenInternalPropertiesToUserJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Log::debug("SenInternalPropertiesToUserJob: 1");

            $users = User::where('status', 1)
                ->with('user_subscription.plan')
                ->where('message_alert', 1)
                ->whereHas('user_subscription', function ($q) {
                    $q->where(function ($q) {
                        $q->where(function ($q) {
                            $q->where('status', UserSubscription::STATUS_ONGOING);
                            $q->whereHas('plan', function ($q) {
                                $q->where('plan_name', 'Professional');
                            });
                        });
                        $q->orWhere(function ($q) {
                            $q->where('status', UserSubscription::STATUS_ONGOING);
                            $q->whereHas('plan', function ($q) {
                                $q->where('plan_name', 'Basic');
                            });
                            $q->where(function ($q) {
                                $q->WhereHas("user_schedule_time", function ($q) {
                                    $q->where(function ($q) {
                                        $currentTimeUTC = $this->getCurrentTimeInUTC('Africa/Johannesburg');
                                        $q->where('start_time', '<=', $currentTimeUTC)->where('end_time', '>=', $currentTimeUTC);
                                    });
                                });
                            });
                        });
                    });
                })
            ;
            $property_columns_keys = array_keys(HelpersProperty::featureColumns());

            $users
                ->each(function (User $user) use ($property_columns_keys) {
                    $user->searchproperty()->with([
                        'user_subscription' => function ($query) {
                            $query->where('status', UserSubscription::STATUS_ONGOING);
                        }
                    ])
                        ->each(function (UserSearchProperty $search) use ($user, $property_columns_keys) {
                            // Skip if there's no ongoing subscription
                            if (!$search->user_subscription || $search->user_subscription->status !== UserSubscription::STATUS_ONGOING) {
                                return;
                            }

                            $search_type_count = $user->searchproperty()->count();
                            if ($search_type_count == 0) {
                                return;
                            }

                            $limit = config('constants.max_properties_to_send_a_day_to_user');
                            $limit = (int) $limit / $search_type_count;

                            // Get properties that match the search criteria

                            $query = InternalProperty::where('propertyStatus', '!=', 'Inactive')
                                ->where('status', 1)
                                ->where(function ($query) use ($search, $property_columns_keys) {
                                    $query->where('country', $search->country)
                                        ->where('province', $search->province_name)

                                        ->where(function ($query) use ($search) {
                                            $query->where('town', $search->city)
                                                ->orWhere('suburb', $search->suburb_name);
                                        })

                                        ->Where('bedrooms', $search->no_of_bedroom)
                                        ->Where('bathrooms', $search->no_of_bathroom)
                                        ->where('propertyType', $search->property_type);

                                    // $query->whereJsonContains('connectivity', ["fiber", "wifi"]);//["WiFiReady", "fiber"]);
                                    foreach ($property_columns_keys as $property_columns_key) {
                                        if ($search->{$property_columns_key}) {
                                            $query->whereJsonContains($property_columns_key, $search->{$property_columns_key});//["WiFiReady", "fiber"]);
                                        }
                                    }
                                })
                                // ->where('price', '>=', $search->start_price)
                                // ->where('price', '<=', $search->end_price)
                                ->whereDoesntHave('sentProperties', function ($query) use ($user) {
                                    $query->where('user_id', $user->id);
                                });

                            $properties = $query->limit($limit)->get();

                            $properties->each(function (InternalProperty $property) use ($search, $user) {

                                $sentProperty = $search->user->sentInternalProperties()->create([
                                    'internal_property_id' => $property->id,
                                    'search_id' => $search->id,
                                ]);

                                $plan_name = $search->user_subscription->plan->plan_name ?? null;

                                if ($plan_name == 'Professional') {
                                    WhatsappTemplate::sendProfessionalPlanMessage($user->country_code, $user->phone, $user->name, 'property-detail?type=internal&property_id=' . $property->id, $property->title, $property->town, $property->suburb);
                                } else {
                                    WhatsappTemplate::sendBasicPlanMessage($user->country_code, $user->phone, $user->name, 'property-detail?type=internal&property_id=' . $property->id, $property->title, $property->town, $property->suburb);
                                }

                                $clientEmail = config('services.agentemail');
                                $propertyListOwner = $property->admin;
                                // Helper::sendAgentsMail([
                                //     'name' => $user->name,
                                //     'email' => $user->email,
                                //     'phone' => $user->country_code . ' ' . $user->phone,
                                // ], [
                                //     'name' => $propertyListOwner->fullName,
                                //     'email' => $propertyListOwner->email,
                                //     // 'email' => "team4pairroxz@gmail.com",
                                // ], [
                                //     'title' => $property->title,
                                //     'description' => $property->description,
                                //     'link' => url('/') . '/property-detail?type=internal&property_id=' . $property->id,
                                // ]);

                            });
                        });
                });

            Log::info('User count: ' . $users->count());
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
    }

    function getCurrentTimeInUTC($timezone = 'Africa/Johannesburg')
    {
        $dateTime = new DateTime('now', new DateTimeZone($timezone));
        $dateTime->setTimezone(new DateTimeZone('UTC'));
        return $dateTime->format('H:i:s');
    }
}
