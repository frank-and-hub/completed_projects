<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Http;

class NoLoremIpsum implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  Closure  $fail
     * @return void
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $loremPatterns = [
            '/\b(lorem ipsum|ipsum|dolor|sit|amet|consectetur|adipiscing|elit)\b/i',
            '/\b(sed|do|eiusmod|tempor|incididunt|ut|labore|et|dolore|magna|aliqua)\b/i',
            '/\b(quis|nostrud|exercitation|ullamco|laboris|nisi|aliquip|ex|ea|commodo)\b/i',
            '/\blorem\b.*\bipsum\b/i',
            '/(.)\1{2,}/',
            '/\b(\w{1,2})\s+(\1\b)+/i',
            '/\b(x{2,}|z{2,}|q{2,}|w{3,})\b/i',
        ];

        $cleanedText = trim(strtolower($value));

        if (strlen($cleanedText) < 3) {
            return;
        }

        foreach ($loremPatterns as $pattern) {
            if (preg_match($pattern, $cleanedText)) {
                $fail("Please fill valid $attribute content.");
                return;
            }
        }

        try {
            $response = Http::get('https://www.purgomalum.com/service/containsprofanity', [
                'text' => $cleanedText
            ]);

            if ($response->successful()) {
                $containsProfanity = $response->body();

                if ($containsProfanity === 'true') {
                    $fail("The $attribute field contains prohibited placeholder or offensive text.");
                }
            } else {
                $fail("Error checking the text for prohibited content.");
            }
        } catch (\Exception $e) {
            $fail("There was an error connecting to the profanity checking service.");
        }
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute field cannot contain lorem ipsum placeholder text.';
    }
}
