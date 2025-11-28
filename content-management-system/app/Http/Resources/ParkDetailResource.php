<?php

namespace App\Http\Resources;

use App\Models\Feature;
use App\Models\FeatureType;
use App\Models\Media;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use stdClass;

class ParkDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $features = $this->features()->pluck('feature_type_id')->toArray();
        $user_id = $request->user() ? $request->user()->id : null;
        $feature_types = FeatureType::whereIn('id', $features)->get();
        foreach ($feature_types as $feature) {
            $feature->park_id = $this->id;
        }
        $total_ratings = $this->ratings()
            ->where('is_verified', 1)
            ->count();
        // $media_ids = $this->park_images()->where('set_as_banner', '0')->whereNull('user_id')->where('status', '1')->limit(5)->pluck('media_id')->toArray();
        $media = $this->park_images()
            ->where('set_as_banner', '0')
            ->where('status', '1')
            ->where(function ($query) {
                $query->where('is_verified', true)
                    ->orWhereNull('user_id');
            })
            ->orderBy('sort_index')
            ->limit(5)
            ->get();
        // $media = Media::whereIn('id', $media_ids)->get();
        // $my_rating =  RatingResource::collection($this->ratings()->Where('user_id', $user_id)->limit(4)->orderBy('created_at', 'desc')->get());
        $my_rating = new RatingResource($this->ratings()
            ->where('user_id', $user_id)
            ->first());

        if (!empty($request->user())) {
            // $my_rating = new RatingResource($this->ratings()->where('user_id', $request->user()->id)->where('is_verified', 1)->first());
            $ratings =  RatingResource::collection($this->ratings()
                ->where('user_id', '!=', $user_id)
                ->where('is_verified', 1)
                ->limit(4)
                ->orderBy('created_at', 'desc')
                ->get());
        } else {
            $ratings =  RatingResource::collection($this->ratings()
                ->where('is_verified', 1)
                ->limit(4)
                ->orderBy('created_at', 'desc')
                ->get());
        }
        $banner = $this->park_images()
            ->where('status', '1')
            ->where('set_as_banner', '1')
            ->first();

        $is_open = false;
        $now_day = strtolower(Carbon::now()->format('l'));
        $today = $this->park_availability()->where('day', $now_day)->first();
        $setting = User::find(1);

        if (empty($today)) {
            $is_open = false;
        } else {
            $now = Carbon::now()->setTimezone($this->timezone);

            if ($today->type == '24_hours') {
                $is_open = true;
            } elseif ($today->type == 'dawn_to_dusk') {
                $dawn = Carbon::createFromTime(5, 0, 0, $this->timezone);
                $dusk = Carbon::createFromTime(19, 0, 0, $this->timezone);

                if ($now->between($dawn, $dusk)) {
                    $is_open = true;
                } else {
                    $is_open = false;
                }
            } elseif ($today->type == 'custom') {
                $opening_time = Carbon::parse($today->opening_time, $this->timezone);
                $closing_time = Carbon::parse($today->closing_time, $this->timezone);

                if ($now->between($opening_time, $closing_time)) {
                    $is_open = true;
                } else {
                    $is_open = false;
                }
            } else {
                $is_open = false;
            }
        }

        $days = $this->park_availability()->pluck('day')->toArray();
        $days_array = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
        $not_included_days = array_diff($days_array, $days);
        $availabilities = $this->park_availability()->get();

        foreach ($not_included_days as $not_included_day) {
            $availability = new stdClass;
            $availability->day =  $not_included_day;
            $availability->type =  'closed';
            $availability->opening_time = null;
            $availability->closing_time = null;
            $availability->availability = 'Closed';
            $availabilities[] = $availability;
        }

        $user = $request->user();
        $bookmark = false;
        if ($user) {
            $b = $this->bookmark()->where('user_id', $user->id)->first();
            $bookmark = $b ? true : false;
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            "banner_image" => $banner ? new MediaResource($banner->media) : null,
            "images" => count($media) > 0 ? ParkMediaResource::collection($media) : null,
            "total_images" => $this->park_images()->where('status', '1')->where(function ($query) {
                $query->where('is_verified', true)->orWhereNull('user_id');
            })->count(),
            "address" => $this->address,
            "city" => $this->city,
            'city_slug' => $this->city_slug,
            "state" => $this->state,
            'state_slug' => $this->state_slug,
            "country" => $this->country,
            'country_short_name' => $this->country_short_name,
            "timezone" => $this->timezone,
            "latitude" => $this->latitude,
            "longitude" => $this->longitude,
            "website" => $this->url,
            "description" => $this->description,
            "features" => FeatureDetailResource::collection($feature_types),
            "is_open" => $is_open,
            "park_availability" => ParkAvailabilityResource::collection($availabilities),
            // "bookmark" => $bookmark,
            "bookmark" => (auth()->check()) ? UserParkBookmarkResource::collection($this->bookmark()->where('user_id', $user->id)->get()) : [],
            "rating" => [
                "total_ratings" => $total_ratings,
                "5" => $total_ratings ? (float) number_format($this->ratings()->where("rating", 5)->where('is_verified', 1)->count() / $total_ratings * 100, 1)  : 0,
                "4" => $total_ratings ? (float) number_format($this->ratings()->where("rating", 4)->where('is_verified', 1)->count() / $total_ratings * 100, 1) : 0,
                "3" => $total_ratings ? (float) number_format($this->ratings()->where("rating", 3)->where('is_verified', 1)->count() / $total_ratings * 100, 1) : 0,
                "2" => $total_ratings ? (float) number_format($this->ratings()->where("rating", 2)->where('is_verified', 1)->count() / $total_ratings * 100, 1) : 0,
                "1" => $total_ratings ? (float) number_format($this->ratings()->where("rating", 1)->where('is_verified', 1)->count() / $total_ratings * 100, 1) : 0,
                "avg_ratings" => (float) number_format($this->ratings()->where('is_verified', 1)->avg('rating'), 1),
            ],
            "reviews" => $ratings,
            "my_review" => $my_rating,
            "park_admission" =>  new ParkAdminssionResource($this),
            "created_at" => Carbon::parse($this->created_at)->timestamp,
            'slug' => $this->slug

        ];
    }
}
