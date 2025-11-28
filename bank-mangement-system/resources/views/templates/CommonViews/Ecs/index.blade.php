<?php
$admin = (auth()->user()->role_id != 3) ? true : false;
$pathLayout = $admin == true ? 'templates.admin.master' : 'layouts.branch.dashboard';
$filter = 'ecs_listing';
$urlDynimic = $admin == true ? '1' : '0';
?>
@extends($pathLayout)
@section('content')
@include("templates.CommonViews.Ecs.$filter")
@section('script')
@include('templates.CommonViews.Ecs.script')
@stop
@stop