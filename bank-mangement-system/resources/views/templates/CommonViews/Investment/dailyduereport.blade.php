@php
    $admin = Auth::user()->role_id != 3 ? true : false;
    $path = $admin == true ? 'templates.admin.master' : 'layouts.branch.dashboard';
    $path2 = $admin == true ? 'templates/CommonViews/Investment/admin/dailydue' : 'templates/CommonViews/Investment/branch/dailydue';
    $path3 = $admin == true ? 'templates/CommonViews/Investment/admin/report_script' : 'templates/CommonViews/Investment/branch/report_script';
@endphp
@extends($path)
@section('content')

    @php
        $dropDown = $company;
        $filedTitle = 'Company';
        $name = 'company_id';
    @endphp

    @if ($admin == true)
        @section('css')
            <style>
                .hideTableData {
                    display: none;
                }
            </style>
        @endsection

        @include($path2)

        @section('script')
            @include($path3)
        @stop
    @else
        @include($path2)
        @section('script')
            @include($path3)
        @stop
    @endif



@stop


<!-- common -->
