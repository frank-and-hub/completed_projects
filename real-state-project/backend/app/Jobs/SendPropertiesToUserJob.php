<?php

namespace App\Jobs;

use App\Helpers\Helper;
use App\Helpers\WhatsappTemplate;
use App\Models\Property;
use App\Models\SentPropertyUser;
use App\Models\User;
use App\Models\UserSearchProperty;
use App\Models\UserSubscription;
use DateTime;
use DateTimeZone;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendPropertiesToUserJob implements ShouldQueue
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

    function getCurrentTimeInUTC($timezone = 'Africa/Johannesburg')
    {
        $dateTime = new DateTime('now', new DateTimeZone($timezone));
        $dateTime->setTimezone(new DateTimeZone('UTC'));
        return $dateTime->format('H:i:s');
    }

    public function handle(): void
    {
        Log::info('SendPropertiesToUserJob started');
        try {
            $users =  User::where('status', 1)
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
                });
            Log::info('User count: ' . $users->count());

            $users->each(function (User $user) {
                // Get only ongoing subscription search properties
                $user->searchproperty()->with(['user_subscription' => function ($query) {
                    $query->where('status', UserSubscription::STATUS_ONGOING);
                }])->each(function (UserSearchProperty $search) use ($user) {
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
                    $query = Property::where('propertyStatus', '!=', 'Inactive')->where(function ($query) use ($search) {
                        $query->where('province', $search->province_name)->where('town', $search->city)->Where('suburb', $search->suburb_name);
                        // $query->where(function ($query) use ($search) {
                        //     $query->where('town', $search->city)
                        //         ->orWhere('suburb', $search->suburb_name);
                        // });

                        if ($search->no_of_bedroom == 5) {
                            $query->orWhere('beds', '>=', $search->no_of_bedroom);
                        } else {
                            $query->where(function ($query) use ($search) {
                                $query->where('beds', $search->no_of_bedroom)
                                    ->orWhereRaw('? BETWEEN FLOOR(beds) AND CEIL(beds)', [$search->no_of_bedroom]);
                            });
                        }
                        if ($search->no_of_bathroom == 5) {
                            $query->orWhere('baths', '>=', $search->no_of_bathroom);
                        } else {
                            $query->where(function ($query) use ($search) {
                                $query->where('baths', $search->no_of_bathroom)
                                    ->orWhereRaw('? BETWEEN FLOOR(baths) AND CEIL(baths)', [$search->no_of_bathroom]);
                            });
                        }
                    })
                        ->where('propertyType', $search->property_type)
                        ->where('price', '>=', $search->start_price)
                        ->where('price', '<=', $search->end_price)
                        ->whereDoesntHave('sentProperties', function ($query) use ($user) {
                            $query->where('user_id', $user->id);
                        });

                    Log::info('search Property: ' . $search);

                    // Get additional features
                    if ($search->additional_features) {
                        Log::info('Additional features: ' . $search->additional_features);
                        $additionalFeatures = json_decode($search->additional_features);
                        $columnMap = Property::$filterColumnMap;
                        $query->where(function ($query) use ($additionalFeatures, $columnMap) {
                            foreach ($additionalFeatures as $feature =>$key) {
                                // Log::info($feature);
                                if (isset($columnMap[$feature]))
                                // Log::info('columnMap data ' . $columnMap[$feature]);
                                // $query->where($columnMap[$feature], 'like', '%' . $key . '%');
                                $query->orWhere($columnMap[$feature],1);
                            }
                        });
                    }
                    $properties = $query->limit($limit)->get();
                    Log::info('Property data ' . $properties);

                    // Send properties to user
                    $properties->each(function (Property $property) use ($search, $user) {
                        Log::info('Property sent to user 1: ' . $property->id);
                        // Send property to user
                        $sentProperty = $search->user->sentProperties()->create([
                            'property_id' => $property->id,
                            'search_id' => $search->id,
                        ]);
                        Log::info('Property sent to user: ' . $property->id);
                        // Send WhatsApp to user
                        $plan_name = $search->user_subscription->plan->plan_name ?? null;
                        if ($plan_name == 'Professional') {
                            Log::info('property title' . $property->title);
                            WhatsappTemplate::sendProfessionalPlanMessage($user->country_code, $user->phone, $user->name, 'property-detail?property_id=' . $property->id, $property->title, $property->town, $property->suburb);
                        } else {
                            WhatsappTemplate::sendBasicPlanMessage($user->country_code, $user->phone, $user->name, 'property-detail?property_id=' . $property->id, $property->title, $property->town, $property->suburb);
                        }
                        Log::info('WhatsApp sent to user: ' . $user->id);
                        // Send Email to client
                        $property->contacts()->each(function ($contact) use ($property, $user) {
                            $clientEmail = config('services.agentemail');
                            Helper::sendAgentsMail([
                                'name' => $user->name,
                                'email' => $user->email,
                                'phone' => $user->country_code . ' ' . $user->phone,
                            ], [
                                'name' => $contact->fullName,
                                'email' => $clientEmail ? $clientEmail : $contact->email,
                                // 'email' => "team4pairroxz@gmail.com",
                            ], [
                                'title' => $property->title,
                                'description' => $property->description,
                                'link' => url('/') . '/property-detail?property_id=' . $property->id,
                            ]);
                        });
                        Log::info('Email sent to client: ' . $user->id);
                        $sentProperty->update([
                            'status' => SentPropertyUser::STATUS_SENT,
                            'message_id' => '123456',
                        ]);
                        Log::info('Property sentProperty: ' . $sentProperty->id);
                    });
                });
            });
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
    }
}
