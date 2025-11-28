@php
    $admin = Auth::user()->role_id != 3 ? true : false;
    $pathLayout = $admin == true ? 'templates.admin.master' : 'layouts.branch.dashboard';
    $pathContent  = 'templates/CommonViews/LoanEmiPayment/content';
    $pathScrip = $admin == true ? 'templates/CommonViews/LoanEmiPayment/partials/script' : 'templates/CommonViews/LoanEmiPayment/partials/branch_script';
@endphp
@extends($pathLayout)
@section('content')    
    @include($pathContent)       
    @section('script')
    @include($pathScrip)
    @stop
@stop
