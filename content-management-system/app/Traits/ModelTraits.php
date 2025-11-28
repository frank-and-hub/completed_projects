<?php

namespace App\Traits;

use App\Models\Feature;
use App\Services\OpenAIService;
use Illuminate\Support\Facades\Log;
use App\Traits\CommonTraits;

trait ModelTraits
{

    use CommonTraits;

    public function generateAndSaveSeoDescription()
    {
        $country = $this->country;
        $state = $this->state;
        $city = $this->city;

        try {
            $openAI = new OpenAIService();
            $featureNames = Feature::whereHas('parks', function ($query) use ($city, $state, $country) {
                $query->where(function ($subQuery) use ($city) {
                    $subQuery->where('city', 'like', "%$city%")
                        ->orWhere('city_slug', $city);
                });
                $query->where(function ($subQuery) use ($state) {
                    $subQuery->where('state', 'like', "%$state%")
                        ->orWhere('state_slug', $state);
                });
                $query->where(function ($subQuery) use ($country) {
                    $subQuery->where('country', 'like', "%$country%")
                        ->orWhere('country_short_name', $country);
                });
            })
                ->pluck('name')
                ->take(6)
                ->unique()
                ->values()
                ->implode(', ');

            $prompt = "Write a 2-paragraph, SEO-optimized description (300–400 words) for a city’s park discovery page. Do not include any specific number of parks in the description.
                The city is $city.
                The most common park features in the city include: $featureNames.
                In paragraph 1, introduce the city as an outdoor-friendly place. you can take few unique information of city available online. Mention the abundance of parks and naturally include 2–3 of the listed features as examples. Highlight its appeal for families, kids, or nature lovers.
                In paragraph 2, expand on what visitors or locals can enjoy in parks with these features. Mention activities (e.g., relaxing, sports, picnics, play) and encourage readers to explore the best parks in $city with these amenities.
                Use a warm, helpful, and engaging tone. Avoid robotic or keyword-stuffed phrasing. Keep the length between 300 and 400 words. and content should not have any grammatical mistakes.  Please DO NOT make all descriptions same for each cities like a template. make it unique, try to take city specific information from google.";
            $seo = $openAI->generateText($prompt);
            $this->update(['seo_description' => $seo]);
            Log::info("this is a : $seo");
            return $seo;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function generateAndStoreFeatureSeoDescription()
    {
        $name = $this->name;
        $list = $this->topFeatureList(true);
        if (in_array($name, $list)) {
            try {
                $openAI = new OpenAIService();
                $sub_feature = $this?->features ?  $this?->features()
                    ->pluck('name')
                    ->values()
                    ->implode(', ') : null;

                $prompt = "Generate a 200-word paragraph introducing the [feature] in [city]. The tone should be conversational, friendly, and relatable for a general local audience.";
                if ($sub_feature) {
                    $prompt .= " Mention sub-features naturally (e.g., equipment types, play elements), but do not explain them in detail.";
                }
                $prompt .= " Do not list or mention specific parks or locations in [city]. The content should feel generalized for any city, and the word “[city]” should remain as a placeholder. End with a warm call to action inviting readers to explore these parks using Parkscape. Optionally include a relatable or realistic use case to bring the experience to life. Do not use bullet points. The final output should be exactly 200 words.";

                $seo = $openAI->generateText($prompt);
                $this->seo()->updateOrCreate(
                    [], // No condition needed if it's morphOne (will auto use the morph keys)
                    [
                        'description' => $seo ?? null
                    ]
                );

                Log::info("this is a : $seo");
                return $seo;
            } catch (\Exception $e) {
                return null;
            }
        } else {
            return null;
        }
    }
}
