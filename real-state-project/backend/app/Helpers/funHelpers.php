<?php

use App\Models\Plans;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Crypt;

/**
 * Helper Functions Documentation
 *
 * This file contains a collection of helper functions used throughout the application.
 *
 * Functions:
 *
 * 1. checkBoxTextUpadte(array|string $locationView): string
 *    - Formats a string or array of strings into a readable format.
 *
 * 2. adminSubUserPlanPrice(): mixed
 *    - Retrieves the price of the "Basic" plan for the authenticated admin user based on their role.
 *
 * 3. p($data): void
 *    - Prints data in a readable format using <pre> tags.
 *
 * 4. pd($data): void
 *    - Prints data in a readable format and terminates the script.
 *
 * 5. numberFormat($number): string
 *    - Formats a number with or without decimal places, depending on its value.
 *
 * 6. findMainImage($media): ?string
 *    - Finds the main image path from a collection of media.
 *
 * 7. truncateTitle(string $string, int $number): string
 *    - Truncates a string to a specific length and appends "..." if needed.
 *
 * 8. dateF($date): string
 *    - Formats a date into a human-readable format with a day suffix (e.g., "1st January 2023").
 *
 * 9. timeF($date): string
 *    - Converts a UTC date to the user's timezone and formats it as time (e.g., "2:30 PM").
 *
 * 10. dateF2($date): string
 *     - Formats a date into a human-readable format with a day suffix and time (e.g., "1st January 2023 2:30 PM").
 *
 * 11. convertToSouthAfricaTime($createdAtUTC, string $timeZone = 'Africa/Johannesburg', bool $time = true): string
 *     - Converts a UTC date to South Africa's timezone and formats it.
 *
 * 12. getCurrencySymbol($currency): string
 *     - Retrieves the currency symbol for a given currency code.
 *
 * 13. creditReportExiredTime(): int
 *     - Returns the expiration time for a credit report in seconds (10 minutes).
 *
 * 14. creditReportencodedbase64(): string
 *     - Encrypts and encodes the expiration timestamp for a credit report.
 *
 * 15. creditReportdecodedbase64(): int
 *     - Decrypts and decodes the expiration timestamp for a credit report.
 *
 * 16. isUrlReachable($url): bool
 *     - Checks if a given URL is reachable by sending a HEAD request.
 */

function checkBoxTextUpadte(array|string $locationView): string
{
    $locationView = is_array($locationView) ? $locationView : [$locationView];
    $word = ucwords($locationView[1] ?? str_replace('_', " ", $locationView[0]));
    return preg_replace('/([a-z])([A-Z])/', '$1 $2', $word);
}

function adminSubUserPlanPrice()
{
    $admin = auth()->user();
    $role = strtolower($admin->designation());
    if ($role == 'landlord') {
        $role = 'privatelandlord';
    }
    $plan = Plans::where('type', $role)->where('plan_name', 'Basic')->first()?->amount;
    return $plan;
}

if (!function_exists('p')) {
    function p($data)
    {
        echo '<pre>';
        print_r($data);
        echo '</pre>';
    }
}

if (!function_exists('pd')) {
    function pd($data)
    {
        p($data);
        die();
    }
}

if (!function_exists('numberFormat')) {
    function numberFormat($number)
    {
        // return number_format(floor($number), 0, '.', ' ');
        return (floor($number) == $number) ? number_format($number, 0, '.', ' ') : number_format($number, 2, '.', ' ');
    }
}
if (!function_exists('findMainImage')) {
    function findMainImage($media)
    {
        $mainImage = collect($media)->firstWhere('isMain', 1);
        return $mainImage ? $mainImage['path'] : null;
    }
}

if (!function_exists('truncateTitle')) {
    /**
     * Truncate a string to a specific length and append "..." if needed.
     *
     * @param string $string
     * @param int $number
     * @return string
     */
    function truncateTitle($string, $number)
    {
        if (strlen($string) > $number) {
            return substr($string, 0, $number) . '...';
        }

        return $string;
    }
}


if (!function_exists('dateF')) {
    function dateF($date)
    {
        $timestamp = strtotime($date);
        $day = date('j', $timestamp);
        $month = date('F', $timestamp);
        $year = date('Y', $timestamp);
        // $time = date('g:i A', $timestamp);

        if ($day % 10 == 1 && $day != 11) {
            $suffix = 'st';
        } elseif ($day % 10 == 2 && $day != 12) {
            $suffix = 'nd';
        } elseif ($day % 10 == 3 && $day != 13) {
            $suffix = 'rd';
        } else {
            $suffix = 'th';
        }
        $formattedDate = $day . $suffix . ' ' . $month . ' ' . $year;
        return $formattedDate;
    }
}

// if (!function_exists('timeF')) {
//     function timeF($date)
//     {
//         $time = date('g:i A', strtotime($date));
//         return $time;
//     }
// }

if (!function_exists('timeF')) {
    function timeF($date)
    {
        $utcCarbon = Carbon::parse($date);
        $userTimezone = auth()->check() && auth()->user()->timezone ? auth()->user()->timezone : 'UTC';
        $localTime = $utcCarbon->setTimezone($userTimezone);
        return $localTime->format('g:i A');
    }
}

if (!function_exists('dateF2')) {
    function dateF2($date)
    {
        $timestamp = strtotime($date);
        $day = date('j', $timestamp);
        $month = date('F', $timestamp);
        $year = date('Y', $timestamp);
        $time = date('g:i A', $timestamp);

        if ($day % 10 == 1 && $day != 11) {
            $suffix = 'st';
        } elseif ($day % 10 == 2 && $day != 12) {
            $suffix = 'nd';
        } elseif ($day % 10 == 3 && $day != 13) {
            $suffix = 'rd';
        } else {
            $suffix = 'th';
        }
        return "{$day}{$suffix} {$month} {$year} {$time}";
    }
}

if (!function_exists('convertToSouthAfricaTime')) {
    function convertToSouthAfricaTime($createdAtUTC, $timeZone = 'Africa/Johannesburg', $time = true)
    {
        $createdAtUTC = Carbon::parse($createdAtUTC);
        if ($time) {
            $createdAtInSouthAfrica = dateF2($createdAtUTC->setTimezone($timeZone)->format('d M y, g:i A'));
        } else {
            $createdAtInSouthAfrica = dateF($createdAtUTC->setTimezone($timeZone)->format('d M y'));
        }
        return $createdAtInSouthAfrica;
    }
}

if (!function_exists('getCurrencySymbol')) {
    function getCurrencySymbol($currency)
    {
        try {
            $fmt = new NumberFormatter('en_US', NumberFormatter::CURRENCY);
            $formatted = $fmt->formatCurrency(0, $currency);
            $currencySymbol = preg_replace('/\d/', '', $formatted);
            return trim($currencySymbol);
        } catch (Exception $e) {
            return 'R';
        }
    }
}

if (!function_exists('creditReportExiredTime')) {
    function creditReportExiredTime()
    {
        return 60 * 10;
    }
}

if (!function_exists('creditReportencodedbase64')) {
    function creditReportencodedbase64()
    {
        return Crypt::encrypt(Carbon::now()->addSecond(creditReportExiredTime())->timestamp);
    }
}

if (!function_exists('creditReportdecodedbase64')) {
    function creditReportdecodedbase64()
    {
        return Crypt::decrypt(Carbon::now()->addSecond(creditReportExiredTime())->timestamp);
    }
}

if (!function_exists('isUrlReachable')) {
    function isUrlReachable($url)
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        try {
            $response = Http::head($url);
            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }
}

if (!function_exists('nameOfWeeks')) {
    function nameOfWeeks()
    {
        return [
            'Monday',
            'Tuesday',
            'Wednesday',
            'Thursday',
            'Friday',
            'Saturday',
            'Sunday',
        ];
    }
}

if (!function_exists('hoursTimeSlots')) {
    function hoursTimeSlots()
    {
        return [
            '00:00:00' => '12:00 AM',
            '01:00:00' => '1:00 AM',
            '02:00:00' => '2:00 AM',
            '03:00:00' => '3:00 AM',
            '04:00:00' => '4:00 AM',
            '05:00:00' => '5:00 AM',
            '06:00:00' => '6:00 AM',
            '07:00:00' => '7:00 AM',
            '08:00:00' => '8:00 AM',
            '09:00:00' => '9:00 AM',
            '10:00:00' => '10:00 AM',
            '11:00:00' => '11:00 AM',
            '12:00:00' => '12:00 PM',
            '13:00:00' => '1:00 PM',
            '14:00:00' => '2:00 PM',
            '15:00:00' => '3:00 PM',
            '16:00:00' => '4:00 PM',
            '17:00:00' => '5:00 PM',
            '18:00:00' => '6:00 PM',
            '19:00:00' => '7:00 PM',
            '20:00:00' => '8:00 PM',
            '21:00:00' => '9:00 PM',
            '22:00:00' => '10:00 PM',
            '23:00:00' => '11:00 PM'
        ];
    }
}


if (!function_exists('propertyTypes')) {
    function propertyTypes()
    {
        return [
            'House',
            'Garden Cottage',
            'Townhouse',
            'Apartment',
            'Business',
            'Cluster',
            'Hotel',
            'Industrial',
            'Mixed Use',
            'Office',
            'Penthouse',
            'Retail',
        ];
    }
}
