<?php

/**
 * Constants for the application
 * 
 * @return array
 * 
 * @uses config('constants')
 */

          return [
               'asert_version'   => env('ASSERT_VERSION', 1),
               'max_properties_to_send_a_day_to_user' => env('MAX_PROPERTIES_TO_SEND_A_DAY_TO_USER', 25),
               'GOOGLE_MAP_KEY'  => env('GOOGLE_MAP_KEY'),
               'GOOGLE_MAP_BACKEND_KEY'  => env('GOOGLE_MAP_BACKEND_KEY'),
               'ENTEGRAL_AREA_URL_COUNTRY'   => env('ENTEGRAL_AREA_URL_COUNTRY', 'South Africa,Namibia,zimbabwe,nigeria,kenya'),
               'PAYFAST_URL'                 => env('PAYFAST_URL')
          ];