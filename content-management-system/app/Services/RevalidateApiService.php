<?php

namespace App\Services;

use App\Jobs\admin\RevalidatePathJob;
use App\Traits\CommonTraits;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class RevalidateApiService
{
    use CommonTraits;

    protected $revalidateKey;
    protected $revalidateUrl;

    public function __construct()
    {
        $this->revalidateKey = config('services.revalidate.key');
        $this->revalidateUrl = config('services.revalidate.url');
    }

    public function revalidate($path)
    {
        // $frontEndUrl = $this->revalidateUrl;
        // $secret = $this->revalidateKey;

        // $url = "$frontEndUrl/api/revalidate?secret=$secret";
        // try {
        //     $response = Http::retry(3, 100)
        //         ->post($url, [
        //             'path' => $path,
        //         ]);
        //     Log::info($response);
        // } catch (\Exception $e) {
        //     Log::error($e->getMessage());
        // }
        RevalidatePathJob::dispatch($path);
        return true;
    }
    public function batch(array $paths): void
    {
        foreach (array_unique($paths) as $path) {
            $this->revalidate($path);
        }
    }

    public function revalidateLocation($location)
    {
        $path = [];
        $location?->loadMissing('containers');
        $path[] = "/$location->country_short_name/$location->state_slug/$location->city_slug";
        // $location->refresh();
        if ($location?->containers) {
            foreach ($location->containers as $container) {
                if ($container->feature) {
                    $featureSlugC = $container->feature()->value('slug');
                    if ($featureSlugC) {
                        $path[] = "/$location->country_short_name/$location->state_slug/$location->city_slug/$featureSlugC";
                    }
                    $featureSlugP = $container->feature_type()->value('slug');
                    if ($featureSlugP) {
                        $path[] = "/$location->country_short_name/$location->state_slug/$location->city_slug/$featureSlugP";
                    }
                }
            }
        }
        $this->batch($path);
    }

    public function revalidateFeature($featureType)
    {
        $path = [];
        $featureType?->loadMissing('features');
        $slug = $featureType->slug;
        $SEOFeatures = $this->metaFeatures();
        if (in_array($slug, $SEOFeatures)) {
            $path[] = "/feature/$slug";
        }
        // foreach ($featureType->parks as $parks) {
        //     $this->revalidatePark($parks);
        // }
        // $featureType->refresh();
        if ($featureType?->features) {
            foreach ($featureType->features as $feature) {
                $featureSlug = $feature->slug;
                if (in_array($featureSlug, $SEOFeatures)) {
                    $path[] = "/feature/$slug/$featureSlug";
                    $path[] = "/feature/$featureSlug";
                }
                // foreach ($feature->parks as $parks) {
                //     $this->revalidatePark($parks);
                // }
            }
        }

        $this->batch($path);
    }

    public function revalidateCategory($category)
    {
        $path = [];
        $category?->loadMissing('subcategories');
        $slug = $category->slug;
        $path[] = "/category/$slug";
        // foreach ($category->parks as $parks) {
        //     $this->revalidatePark($parks);
        // }
        // $category->refresh();
        if ($category?->subcategories) {
            foreach ($category->subcategories as $subcategory) {
                $subcategory = $subcategory->slug;
                $path[] = "/category/$slug/$subcategory";
                $path[] = "/category/$subcategory";
                // foreach ($category->parks as $parks) {
                //     $this->revalidatePark($parks);
                // }
            }
        }
        $this->batch($path);
    }

    public function revalidatePark($park)
    {
        $path = [];
        $slug = $park->slug;
        $country = $park->country_short_name;
        $state = $park->state_slug;
        $city = $park->city_slug;

        $path[] = "/$country/$state/$city";
        $path[] = "/$country/$state/$city/$slug";
        $path[] = "/park/$country/$state/$city/$slug";
        $path[] = "/park/$country/$state/$city/$slug/gallery";

        $this->batch($path);
    }
}
