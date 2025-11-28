<?php
    $admin = (auth()->user()->role_id != 3) ? true : false; 
    $pathLayout = $admin == true ? 'templates.admin.master' : 'layouts.branch.dashboard';
    $filter = $admin == true ? 'adminFilter' : 'branchFilter';
?>
@extends($pathLayout)
@section('content')
    @include("templates.CommonViews.DayBusinessReport.$filter")       
    @section('script')
    @include('templates.CommonViews.DayBusinessReport.script')
    @stop
@stop