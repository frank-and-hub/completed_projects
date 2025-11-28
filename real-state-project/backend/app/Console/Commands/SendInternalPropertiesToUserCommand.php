<?php

namespace App\Console\Commands;

use App\Helpers\Helper;
use Illuminate\Console\Command;
use App\Models\User;
use App\Models\UserSubscription;
use App\Helpers\Property as HelpersProperty;
use App\Helpers\WhatsappTemplate;
use App\Models\Admin;
use App\Models\InternalProperty;
use App\Models\UserSearchProperty;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SendInternalPropertiesToUserCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    // php artisan app:sen-internal-properties-to-user-command
    protected $signature = 'app:send-internal-properties-to-user';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Match internal properties to users search criteria, and notify users through WhatsApp templates while sending an email to the agent or private landlord associated with the property';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $users = $this->getUsersEligibleForPropertyMatch();
            if ($users->count() == 0) {
                $this->info_('No eligible users found for property matching.');
                return;
            }

            Log::debug("SendInternalPropertiesToUserCommand 1");

            $this->info_('');
            $this->info_('Users');
            $this->info_($users->get()->pluck('email'));

            //columns
            $property_columns_keys = array_keys(HelpersProperty::featureColumns());

            $limit = config('constants.max_properties_to_send_a_day_to_user');

            $users->each(function (User $user) use ($property_columns_keys, $limit) {
                $this->info_('------------------------------------------------------');
                $this->info_('');
                $searchproperty = $user->searchproperty()
                    // ->where('id', '9e698f16-78c1-4221-ac8b-afba2c606a3b')
                    ->whereHas('user_subscription', function ($query) {
                        $query->where('status', UserSubscription::STATUS_ONGOING);
                    })->with('user_subscription');

                $search_type_count = $searchproperty->count();

                $this->info_('user Email:' . $user->email);
                $this->info_('user search property');
                $this->info_('searchproperty id:' . $searchproperty->pluck('id'));

                $searchproperty->each(function (UserSearchProperty $search) use ($user, $property_columns_keys, $limit, $search_type_count) {
                    $limit = (int) $limit / $search_type_count;

                    $properties = $this->internalProperties($search, $property_columns_keys, $user, $limit);

                    $this->info_('user search property id: ' . $search->id);
                    $this->info_('internal properties');
                    $this->info_('internal properties id : ' . $properties->pluck('id'));
                    // print_r( $properties->toArray());
                    $properties->each(function (InternalProperty $property) use ($search, $user) {
                        $sentProperty = $search->user->sentInternalProperties()->create([
                            'internal_property_id' => $property->id,
                            'search_id' => $search->id,
                            'admin_id' => $property->admin_id
                        ]);
                        $this->sendWhatsAppTemplate_tenant($search, $user, $property);
                        // $this->sendEmailToAdminSubUser($property, $user);
                    });
                });
            });

            $this->info_('User count: ' . $users->count());
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
    }

    function info_($data)
    {
        $this->info($data);
        Log::debug($data);
    }

    function sendEmailToAdminSubUser($property, $user)
    {
        $clientEmail = config('services.agentemail');
        $propertyListOwner = $property->admin;
        Helper::sendAgentsMail([
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->country_code . ' ' . $user->phone,
        ], [
            'name' => $propertyListOwner->fullName,
            'email' => $propertyListOwner->email,
            // 'email' => "team4pairroxz@gmail.com",
        ], [
            'title' => $property->title,
            'description' => $property->description,
            'link' => url('/') . '/property-detail?updateKey=internal&property_id=' . $property->id,
        ]);
    }

    function sendWhatsAppTemplate_tenant($search, $user, $property)
    {
        $plan_name = $search?->user_subscription?->plan?->plan_name ?? null;
        $this->info_($plan_name . 'while sending message to tenant');
        $admin_user = Admin::findOrFail($property->admin_id);
        Log::debug('SendInternalPropertiesToUserCommand sendWhatsAppTemplate_tenant');
        $property_link = "property-detail?updateKey=internal&property_id=$property->id";

        match ($plan_name) {
            'Professional' => WhatsappTemplate::sendProfessionalPlanMessage($user->country_code, $user->phone, $user->name, $property_link, $property->title, $property->town, $property->suburb),
            default => WhatsappTemplate::sendBasicPlanMessage($user->country_code, $user->phone, $user->name, $property_link, $property->title, $property->town, $property->suburb)
        };

        $this->sendWhatsAppTemplate_agent($admin_user, $property_link);
    }

    function sendWhatsAppTemplate_agent($admin, $property_link)
    {
        Log::info('agent receive message on ' . $admin->phone);
        if ($admin->hasRole('agent') && $admin->is_whatsapp_notification) {
            WhatsappTemplate::supplyPropertyMatchMessage($admin->dial_code, $admin->phone, $admin->name, $property_link);
        } elseif ($admin->hasRole('privatelandlord')) {
            // $slot = $admin->adminScheduleTime()->first();

            // if ($slot) {
            //     $currentTimeUTC = Carbon::now()->format('H:i:s');
            //     if ($slot->start_time <= $currentTimeUTC && $slot->end_time >= $currentTimeUTC) {
            //         WhatsappTemplate::supplyPropertyMatchMessage($admin->dial_code, $admin->phone, $admin->name, $property_link);
            //     }
            // } else {
            if ($admin->is_whatsapp_notification) {
                WhatsappTemplate::supplyPropertyMatchMessage($admin->dial_code, $admin->phone, $admin->name, $property_link);
            }
            // }
        }
    }

    function internalProperties_old($search, $property_columns_keys, $user, $limit)
    {
        $query = InternalProperty::where('propertyStatus', '!=', 'Inactive')
            ->where(function ($query) use ($search, $property_columns_keys) {
                $query
                    ->where('financials->price', '>=', (int) $search->start_price)
                    ->where('financials->price', '<=', (int) $search->end_price)

                    ->where('country', $search->country)->where('province', $search->province_name)->where('town', $search->city)->where('suburb', $search->suburb_name);

                $query = $query->where('propertyType', $search->property_type);

                $query->where(function ($query) use ($search, $property_columns_keys) {

                    if ($search->no_of_bedroom == 5) {
                        $query->orWhere('bedrooms', '>=', $search->no_of_bedroom);
                    } else {
                        $query->where(function ($query) use ($search) {
                            $query->where('bedrooms', $search->no_of_bedroom)
                                ->orWhereRaw('? BETWEEN FLOOR(bedrooms) AND CEIL(bedrooms)', [$search->no_of_bedroom]);
                        });
                    }
                    if ($search->no_of_bathroom == 5) {
                        $query->orWhere('bathrooms', '>=', $search->no_of_bathroom);
                    } else {
                        $query->where(function ($query) use ($search) {
                            $query->where('bathrooms', $search->no_of_bathroom)
                                ->orWhereRaw('? BETWEEN FLOOR(bathrooms) AND CEIL(bathrooms)', [$search->no_of_bathroom]);
                        });
                    }


                    // $query->whereJsonContains('connectivity', ["fiber", "wifi"]);//["WiFiReady", "fiber"]);
                    foreach ($property_columns_keys as $property_columns_key) {
                        if ($search->{$property_columns_key}) {
                            $query = $query->orwhereJsonContains($property_columns_key, $search->{$property_columns_key}); //["WiFiReady", "fiber"]);
                        }
                    }
                });
            })
            ->whereDoesntHave('sentProperties', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            });


        return $query->limit($limit)->get();
    }

    function internalProperties($search, $property_columns_keys, $user, $limit)
    {
        $this->info_($search);
        $this->info_($search->start_price);
        $this->info_($search->end_price);
        $query = InternalProperty::where('propertyStatus', '!=', 'Inactive')
            ->whereDoesntHave('sentProperties', function ($query) use ($user, $search) {
                $query->where('user_id', $user->id)
                    // ->where('search_id', $search->id)
                ;
            })
            ->where('status', 1)
            ->where(function ($query) use ($search, $property_columns_keys) {
                $query
                    ->where('financials->price', '>=', (int) $search->start_price)
                    ->where('financials->price', '<=', (int) $search->end_price)
                ;
            })
            ;
        $this->info_("1");
        $query = $query->where(function ($query) use ($search, $property_columns_keys) {
            $query = $query
                ->where('country', $search->country)
                ->where('province', $search->province_name)
                ->where('town', $search->city)
                ->where('suburb', $search->suburb_name);
        });
        $this->info_("2");
        $query = $query->where(function ($que) use ($search, $property_columns_keys) {
            $que->where('propertyType', $search->property_type);
        });
        $this->info_("3");
        $query = $query->where(function ($query) use ($search, $property_columns_keys) {
            $query->where(function ($query) use ($search, $property_columns_keys) {
                if ($search->no_of_bedroom == 5) {
                    $query->orWhere('bedrooms', '>=', $search->no_of_bedroom);
                } else {
                    $query->where(function ($query) use ($search) {
                        $query->where('bedrooms', $search->no_of_bedroom)
                            ->orWhereRaw('? BETWEEN FLOOR(bedrooms) AND CEIL(bedrooms)', [$search->no_of_bedroom]);
                    });
                }
                if ($search->no_of_bathroom == 5) {
                    $query->orWhere('bathrooms', '>=', $search->no_of_bathroom);
                } else {
                    $query->where(function ($query) use ($search) {
                        $query->where('bathrooms', $search->no_of_bathroom)
                            ->orWhereRaw('? BETWEEN FLOOR(bathrooms) AND CEIL(bathrooms)', [$search->no_of_bathroom]);
                    });
                }
                foreach ($property_columns_keys as $property_columns_key) {
                    if ($search->{$property_columns_key}) {
                        $query = $query->orwhereJsonContains($property_columns_key, $search->{$property_columns_key});
                    }
                }
            });
        });
        return $query->limit($limit)->get();
    }

    function getUsersEligibleForPropertyMatch()
    {
        $data = User::where([
            'status' => 1,
            'message_alert' => 1
        ])
            ->with([
                'user_subscription',
                'searchproperty',
                'user_subscription.plan'
            ])
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
                            $q->whereDoesntHave('user_schedule_time')
                                ->orWhereHas("user_schedule_time", function ($q) {
                                    $q->where(function ($q) {
                                        $currentTimeUTC = Carbon::now()->format('H:i:s');
                                        $q->where('start_time', '<=', $currentTimeUTC)
                                            ->where('end_time', '>=', $currentTimeUTC);
                                    });
                                });
                        });
                    });
                });
            });
        return $data;
    }
}
