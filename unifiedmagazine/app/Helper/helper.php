<?php
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
        echo '<pre>';
        print_r($data);
        echo '</pre>';
        die();
    }
}

if (!function_exists('date_format')) {
    function date_formate($date, $format)
    {
        $formattedDate = date($format, srttotime($date));
        return $formattedDate;
    }
}

if (!function_exists('current_date')) {
    function current_date($date)
    {
        return date('d M Y', strtotime($date));
    }
}

if (!function_exists('current_month')) {
    function current_month($date)
    {
        return date('F', strtotime($date));
    }
}

if (!function_exists('current_year')) {
    function current_year($date)
    {
        return date('Y', strtotime($date));
    }
}

date_default_timezone_set('Asia/Kolkata');

if (!function_exists('currentDate')) {
    function currentDate()
    {
        return date('d-m-Y-h:i:s:a');
    }
}
if (!function_exists('lines')) {
    function lines($a)
    {
        $data = "<h3>".$a."</h3><img src=" . asset('view/img/lines.svg')  . " class='img-lines' alt='lines'>";
        return $data;
    }
}
if (!function_exists('auth_user_tech_title')) {
    function auth_user_tech_title($id)
    {
        $data = \App\Models\WorkTechnology::whereUserId($id)->pluck('title','id');
        return $data;
    }
}

if(!function_exists('app_password')){
    function app_password(){
        $data = \App\Models\Login::whereUserId(Auth::user()->id)->first();
        return $data ? $data->password : null;
    }
}

if(!function_exists('auth_name')){
    function auth_name(){
        $data = ucwords(Auth::user()?->name);
        return $data;
    }
}

if(!function_exists('url_last')){
    function url_last($url){
        $data = collect(explode('/', $url))->last();
        return $data;
    }
}

if(!function_exists('authrole')){
    function authrole(){
        $data = (Auth::user()->role) == '1';
        return $data;
    }
}

?>
