<?php
    $admin = (auth()->user()->role_id != 3) ? true : false; 
    $pathLayout = $admin == true ? 'templates.admin.master' : 'layouts.branch.dashboard'; 
    $comman = $admin == true ? '1' : '0';  
?>
@extends($pathLayout)
@section('content')
    @include("templates.CommonViews.Employee.register")
    @section('script')
    @include('templates.CommonViews.Employee.script')
    @stop
@stop