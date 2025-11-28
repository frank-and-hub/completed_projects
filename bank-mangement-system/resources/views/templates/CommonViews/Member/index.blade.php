@php
    $admin = Auth::user()->role_id != 3 ? true : false;
    $pathLayout = $admin == true ? 'templates.admin.master' : 'layouts.branch.dashboard';
	$pathcontent = 'templates/CommonViews/Member/content';
    $pathScrip = $admin == true ? 'templates/CommonViews/Member/script_a' : 'templates/CommonViews/Member/script_b';
    $branch_chk = $admin == true ? 0 : 1;
@endphp
@extends($pathLayout)
@section('content')   
@include($pathcontent)   
    @section('script')
    @include($pathScrip)
    @stop
@stop
