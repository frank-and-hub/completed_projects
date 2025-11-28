@extends('templates.admin.master')

@section('content')

<?php
$logsData = \App\Models\HolidayNotificationSetting::get();
use Carbon\Carbon;

$today = Carbon::now()->format('Y-m-d');
// $crons = \App\Models\HolidayNotificationSetting::where('status',0)->where(date('Y', strtotime(convertDate('cron_date'))) != date('Y'))->first();

$crons = \App\Models\HolidayNotificationSetting::where('status', 0)
    ->orWhere(function($query) {
        $query->whereYear('cron_date', '!=', date('Y'));
    })
    ->first();



?>

<div class="content">
    <div class="row">

        @if(count($status) > 0)
            @foreach($status as $contents)
                <div class="col-md-4">
                    <div class="card border-left-3 border-left-violet rounded-left-0">
                        <div class="card-body">
                            <div class="d-sm-flex align-item-sm-center flex-sm-nowrap">
                                <div>
                                    <h6 class="font-weight-semibold">{{ $contents['title'] }}</h6>
                                    <ul class="list list-unstyled mb-0">
                                        @foreach($contents['data'] as $content)
                                            <li>{{ $content['label'] }}: <span class="font-weight-semibold text-default">#{{ $content['count'] }}</span></li>
                                        @endforeach
                                    </ul>
                                </div>

                                <div class="text-sm-right mb-0 mt-3 mt-sm-0 ml-auto">
                                    <h3 class="font-weight-semibold">#{{ $contents['total'] }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        @endif
   
        @if(isset($crons))
        <marquee behavior="scroll" direction="left" style="color:red;">
            <h4>Your Holidays cron is not updated for the current year. Please update.</h4>
        </marquee>

        <div class="col-md-12">
            <div class="card border-left-3 border-left-violet rounded-left-0">
                <div class="card-body">
                    <div class="d-sm-flex align-item-sm-center flex-sm-nowrap">
                        <div>
                            <h6 class="font-weight-semibold">Holiday Crons Status</h6>
                            <ul class="list list-unstyled mb-0">
                                @foreach($logsData as $content)
                                    @if(date('Y', strtotime(convertDate($content->cron_date))) != date('Y') &&  ($content->status== 0))
                                        <a href="{{ route('admin.allholiday.crons') }}" style="text-decoration:none;">
                                            <div class="row">
                                                <div class="col-12">
                                                    <span style="float: left;">{{ $content->title }}   - </span>
                                                    <span style="color: red; float: right;">  Please update for the current year & status.</span>
                                                </div>
                                            </div>
                                        </a>

                                    @elseif(date('Y', strtotime(convertDate($content->cron_date))) != date('Y') &&  ($content->status== 1))
                                        <a href="{{ route('admin.allholiday.crons') }}" style="text-decoration:none;">
                                            <div class="row">
                                                <div class="col-12">
                                                    <span style="float: left;">{{ $content->title }}   - </span>
                                                    <span style="color: red; float: right;"> Please update for the current year.</span>
                                                </div>
                                            </div>
                                        </a>

                                        @elseif(date('Y', strtotime(convertDate($content->cron_date))) == date('Y') &&  ($content->status== 0))
                                        <a href="{{ route('admin.allholiday.crons') }}" style="text-decoration:none;">
                                            <div class="row">
                                                <div class="col-12">
                                                    <span style="float: left;">{{ $content->title }}   - </span>
                                                    <span style="color: red; float: right;"> Status is inactive currently.</span>
                                                </div>
                                            </div>
                                        </a>
                                    @endif

                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

</div>

@stop
