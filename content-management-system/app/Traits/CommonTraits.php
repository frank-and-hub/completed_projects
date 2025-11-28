<?php

namespace App\Traits;

use App\Models\Feature;
use App\Models\FeatureType;
use App\Models\Parks;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

trait CommonTraits
{
    public function makeSlug($country = null, $state = null, $city = null, $name = null)
    {
        $data = collect([$country, $state, $city, $name])
            ->filter()
            ->map(function ($part) {
                return Str::slug($part);
            });

        return $data->implode('_');
    }

    public function generateUniqueSlug($baseSlug, $currentId = null, $model = Parks::class, $column = 'slug')
    {
        // $slug = $baseSlug;
        // $counter = 1;

        // while (
        //     Parks::where('slug', $slug)
        //     ->when($currentId, fn($q) => $q->where('id', '!=', $currentId)) // Exclude self
        //     ->exists()
        // ) {
        //     $slug = $baseSlug . '-' . $counter;
        //     $counter++;
        // }

        // return $slug;

        $baseSlug = Str::slug($baseSlug);
        $slug = $baseSlug;

        $query = $model::where($column, 'LIKE', "{$baseSlug}%");

        if ($currentId) {
            $query->where('id', '!=', $currentId);
        }

        $allSlugs = $query->pluck($column)->toArray();

        if (!in_array($slug, $allSlugs)) {
            return $slug;
        }

        $max = 1;

        foreach ($allSlugs as $existingSlug) {
            if (preg_match('/^' . preg_quote($baseSlug, '/') . '-(\d+)$/', $existingSlug, $matches)) {
                $num = (int) $matches[1];
                if ($num >= $max) {
                    $max = $num + 1;
                }
            }
        }

        return "{$baseSlug}-{$max}";
    }

    public function findPark($slug = null, $country = null, $state = null, $city = null)
    {
        return Parks::when($slug, function ($q) use ($slug) {
            $q->where('slug', $slug);
        })->when($country, function ($query) use ($country) {
            $query->where(function ($subQuery) use ($country) {
                $subQuery->where('country', 'like', "%$country%")
                    ->orWhere('country_short_name', $country);
            });
        })->when($state, function ($query) use ($state) {
            $query->where(function ($subQuery) use ($state) {
                $subQuery->where('state', 'like', "%$state%")
                    ->orWhere('state_slug', $state);
            });
        })->when($city, function ($query) use ($city) {
            $query->where(function ($subQuery) use ($city) {
                $subQuery->where('city', 'like', "%$city%")
                    ->orWhere('city_slug', $city);
            });
        })->first();
    }

    public function normalizeSearchTerm($input)
    {
        // 1. Convert to lowercase
        $input1 = strtolower($input);

        // 2. Replace multiple spaces with a single space
        $input2 = preg_replace('/\s+/', ' ', $input1);
        // 3. Replace & with and
        $input3 = str_replace('&', 'and', $input2);

        $input4 = str_replace(["'"], '', $input3);

        // 4. Keep letters, numbers, spaces, and hyphens
        $input5 = preg_replace('/[^a-z0-9 \-]/', ' ', $input4);

        // 5. Optional: Replace certain characters with space
        $input6 = str_replace(['~', '.'], ' ', $input5);

        // 6. Trim leading/trailing whitespace
        $input7 = trim($input6);

        return $input7;
    }

    public function topFeatureList($type = false)
    {
        $cacheKey = $type ? 'top_features_with_type' : 'top_features';
        return Cache::remember($cacheKey, now()->addDay(), function () use ($type) {
            $data = [
                'Playgrounds',
                'Baseball Field',
                'Basketball Courts',
                'Football Field',
                'Pickleball Courts',
                'Skateboard Park',
                'Soccer Field',
                'Street Hockey Rink',
                'Tennis Courts',
                'Track and Field',
                'Outdoor Gyms',
                'Pull Up Bars',
                'Sit Up Bench',
                'Biking Trails (Bike Paths)',
                'Hiking Trails',
                'Mountain Biking Trails',
                'Nature Walk',
                'Walking Paths',
                'Off Leash Dog Park (Enclosed Space)',
                'Off Leash Dog Parks (Open Space)',
                'Beach',
                'Boat Launch Ramp',
                'Boat Rentals',
                'Boat Tour',
                'Canoeing (Kayaking)',
                'Fishing',
                'Lake',
                'Splash Pad (Spray Park)',
                'Swimming',
                'Swimming Pool (Outdoor)',
                'BBQs/Barbeques (Grill On-Site)',
                'Chess Tables',
                'Community Gardens',
                'Cross Country Skiing',
                'Ice Skating Rink (Outdoor)',
                'Sledding (Tobogganing)',
                'Campground (Campsite)',
                'RV Parking',
                'Arboretum',
                'Botanical Gardens',
                'Memorial (Monument)',
                'Scenic Viewpoint (Lookout)',
                'Sculptures',
                'Statues',
                'Water Fountain',
                'Waterfall',
                'Wildlife Sanctuary (Nature Preserve)',
                'Bar (Beer Garden)',
                'Concession Stands (Snack Bar)',
                'Performance Stage (Bandshell)',
            ];
            return $this->getSpecificData($data, 'name', $type);
        });
    }

    public function metaFeatures($type = false)
    {
        $cacheKey = $type ? 'meta_features_with_type' : 'meta_features';
        return Cache::remember($cacheKey, now()->addDay(), function () use ($type) {
            $data = [
                'Playgrounds',
                'Baseball Fields',
                'Basketball Courts',
                'Football Fields',
                'Pickleball Courts',
                'Skateboard Parks',
                'Soccer Fields',
                'Street Hockey Rinks',
                'Tennis Courts',
                'Track and Fields',
                'Outdoor Gyms',
                'Pull Up Bars',
                'Sit Up Benches',
                'Biking Trails',
                'Hiking Trails',
                'Mountain Biking Trails',
                'Nature Walk',
                'Walking Paths',
                'Off Leash Dog Parks',
                'Beaches',
                'Boat Launch Ramps',
                'Boat Rentals',
                'Boat Tours',
                'Kayaking',
                'Fishing',
                'Lakes',
                'Splash Pads',
                'Swimming Pools',
                'BBQ Grills',
                'Chess Tables',
                'Community Gardens',
                'Gazebos',
                'Picnic Pavilions',
                'Picnic Tables',
                'Cross Country Skiing',
                'Ice Skating Rinks',
                'Sledding',
                'Campgrounds',
                'RV Parks',
                'Arboretums',
                'Botanical Gardens',
                'Memorials',
                'Scenic Viewpoints',
                'Sculptures',
                'Statues',
                'Water Fountains',
                'Waterfalls',
                'Wildlife Sanctuaries',
                'Beer Gardens',
                'Concession Stands',
                'Performance Stages',
                'Restaurants',
            ];
            return $this->getSpecificData($data, 'slug', $type);
        });
    }

    public function getSpecificData($data, $column, $type = false)
    {
        $values = array_map(function ($val) use ($column) {
            return $column === 'slug' ? Str::slug($val) : $val;
        }, $data);

        // Fetch all FeatureTypes and Features in one go
        $featureTypes = FeatureType::with('features:id,name,slug')
            ->whereIn($column, $values)
            ->get()
            ->keyBy($column);

        $remainingValues = array_diff($values, $featureTypes->pluck($column)->toArray());
        $features = Feature::whereIn($column, $remainingValues)
            ->get()
            ->keyBy($column);

        // Build the result
        $newData = [];
        foreach ($values as $value) {
            if (isset($featureTypes[$value])) {
                foreach ($featureTypes[$value]->features as $feature) {
                    $newData[] = $feature->$column;
                }
                if ($type) {
                    $newData[] = $value;
                }
            } elseif (isset($features[$value])) {
                $newData[] = $features[$value]->$column;
            }
        }

        return array_unique($newData);
    }

    public function getFilteredParks(?string $slug = null, ?string $city = null, ?string $state = null, ?string $country = null, ?bool $type = false): Builder
    {
        $query = Parks::with('park_images', 'ratings');

        if ($slug) {
            $query->where(function ($q) use ($slug) {
                $q->whereHas('features.feature', fn($q) => $q->where('slug', $slug))
                    ->orWhereHas('features.feature_type', fn($q) => $q->where('slug', $slug));
            });
        }

        if ($city) {
            $query->where(function ($query) use ($city, $type) {
                $query->where('city_slug', $city);
                if ($type) {
                    $query->orWhere('city', $city);
                }
            });
        }

        if ($state) {
            $query->where(function ($query) use ($state, $type) {
                $query->where('state_slug', $state);
                if ($type) {
                    $query->orWhere('state', $state);
                }
            });
        }

        if ($country) {
            $query->where(function ($query) use ($country, $type) {
                $query->where('country_short_name', $country);
                if ($type) {
                    $query->orWhere('country', $country);
                }
            });
        }

        return $query;
    }
}
