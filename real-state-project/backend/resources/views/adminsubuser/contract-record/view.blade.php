@extends('layout.master')
@section('content')
@section('title', __('PocketProperty | ' . $active_page))
    @push('custom-css')
        <style>
            .timeline-centered {
                position: relative;
                margin-bottom: 30px;
            }

            .timeline-centered.timeline-sm .timeline-entry {
                margin-bottom: 20px !important;
            }

            .timeline-centered.timeline-sm .timeline-entry .timeline-entry-inner .timeline-label {
                padding: 1em;
            }

            .timeline-centered:before,
            .timeline-centered:after {
                content: " ";
                display: table;
            }

            .timeline-centered:after {
                clear: both;
            }

            .timeline-centered:before {
                content: '';
                position: absolute;
                display: block;
                width: 7px;
                background: #ffffff;
                left: 50%;
                top: 20px;
                bottom: 20px;
                margin-left: -4px;
            }

            .timeline-centered .timeline-entry {
                position: relative;
                width: 50%;
                float: right;
                margin-bottom: 70px;
                clear: both;
            }

            .timeline-centered .timeline-entry:before,
            .timeline-centered .timeline-entry:after {
                content: " ";
                display: table;
            }

            .timeline-centered .timeline-entry:after {
                clear: both;
            }

            .timeline-centered .timeline-entry.begin {
                margin-bottom: 0;
            }

            .timeline-centered .timeline-entry.left-aligned {
                float: left;
            }

            .timeline-centered .timeline-entry.left-aligned .timeline-entry-inner {
                margin-left: 0;
                margin-right: -28px;
            }

            .timeline-centered .timeline-entry.left-aligned .timeline-entry-inner .timeline-time {
                left: auto;
                right: -115px;
                text-align: left;
            }

            .timeline-centered .timeline-entry.left-aligned .timeline-entry-inner .timeline-icon {
                float: right;
            }

            .timeline-centered .timeline-entry.left-aligned .timeline-entry-inner .timeline-label {
                margin-left: 0;
                margin-right: 85px;
            }

            .timeline-centered .timeline-entry.left-aligned .timeline-entry-inner .timeline-label:after {
                left: auto;
                right: 0;
                margin-left: 0;
                margin-right: -9px;
                -moz-transform: rotate(180deg);
                -o-transform: rotate(180deg);
                -webkit-transform: rotate(180deg);
                -ms-transform: rotate(180deg);
                transform: rotate(180deg);
            }

            .timeline-centered .timeline-entry .timeline-entry-inner {
                position: relative;
                margin-left: -31px;
            }

            .timeline-centered .timeline-entry .timeline-entry-inner:before,
            .timeline-centered .timeline-entry .timeline-entry-inner:after {
                content: " ";
                display: table;
            }

            .timeline-centered .timeline-entry .timeline-entry-inner:after {
                clear: both;
            }

            .timeline-centered .timeline-entry .timeline-entry-inner .timeline-time {
                position: absolute;
                left: -115px;
                text-align: right;
                padding: 10px;
                -webkit-box-sizing: border-box;
                -moz-box-sizing: border-box;
                box-sizing: border-box;
            }

            .timeline-centered .timeline-entry .timeline-entry-inner .timeline-time>span {
                display: block;
            }

            .timeline-centered .timeline-entry .timeline-entry-inner .timeline-time>span:first-child {
                font-size: 18px;
                font-weight: bold;
            }

            .timeline-centered .timeline-entry .timeline-entry-inner .timeline-time>span:last-child {
                font-size: 12px;
            }

            .timeline-centered .timeline-entry .timeline-entry-inner .timeline-icon {
                background: #fff;
                color: #999999;
                display: block;
                width: 60px;
                height: 60px;
                -webkit-background-clip: padding-box;
                -moz-background-clip: padding-box;
                background-clip: padding-box;
                border-radius: 50%;
                text-align: center;
                border: 7px solid #ffffff;
                line-height: 45px;
                font-size: 15px;
                float: left;
            }

            .timeline-centered .timeline-entry .timeline-entry-inner .timeline-icon.bg-primary {
                background-color: #dc6767;
                color: #fff;
            }

            .timeline-centered .timeline-entry .timeline-entry-inner .timeline-icon.bg-success {
                background-color: #5cb85c;
                color: #fff;
            }

            .timeline-centered .timeline-entry .timeline-entry-inner .timeline-icon.bg-info {
                background-color: #5bc0de;
                color: #fff;
            }

            .timeline-centered .timeline-entry .timeline-entry-inner .timeline-icon.bg-warning {
                background-color: #f0ad4e;
                color: #fff;
            }

            .timeline-centered .timeline-entry .timeline-entry-inner .timeline-icon.bg-danger {
                background-color: #d9534f;
                color: #fff;
            }

            .timeline-centered .timeline-entry .timeline-entry-inner .timeline-icon.bg-red {
                background-color: #bf4346;
                color: #fff;
            }

            .timeline-centered .timeline-entry .timeline-entry-inner .timeline-icon.bg-green {
                /* background-color: #488c6c; */
                background-color: #f30051;
                color: #fff;
            }

            .timeline-centered .timeline-entry .timeline-entry-inner .timeline-icon.bg-blue {
                background-color: #0a819c;
                color: #fff;
            }

            .timeline-centered .timeline-entry .timeline-entry-inner .timeline-icon.bg-yellow {
                background-color: #f2994b;
                color: #fff;
            }

            .timeline-centered .timeline-entry .timeline-entry-inner .timeline-icon.bg-orange {
                /* background-color: #e9662c; */
                background-color: #808080;
                color: #fff;
            }

            .timeline-centered .timeline-entry .timeline-entry-inner .timeline-icon.bg-pink {
                background-color: #bf3773;
                color: #fff;
            }

            .timeline-centered .timeline-entry .timeline-entry-inner .timeline-icon.bg-violet {
                background-color: #9351ad;
                color: #fff;
            }

            .timeline-centered .timeline-entry .timeline-entry-inner .timeline-icon.bg-grey {
                background-color: #4b5d67;
                color: #fff;
            }

            .timeline-centered .timeline-entry .timeline-entry-inner .timeline-icon.bg-dark {
                background-color: #594857;
                color: #fff;
            }

            .timeline-centered .timeline-entry .timeline-entry-inner .timeline-label {
                position: relative;
                background: #ffffff;
                padding: 1.7em;
                margin-left: 85px;
                -webkit-background-clip: padding-box;
                -moz-background-clip: padding;
                background-clip: padding-box;
                -webkit-border-radius: 3px;
                -moz-border-radius: 3px;
                border-radius: 3px;
            }

            .timeline-centered .timeline-entry .timeline-entry-inner .timeline-label.bg-red {
                background: #bf4346;
            }

            .timeline-centered .timeline-entry .timeline-entry-inner .timeline-label.bg-red:after {
                border-color: transparent #bf4346 transparent transparent;
            }

            .timeline-centered .timeline-entry .timeline-entry-inner .timeline-label.bg-red .timeline-title,
            .timeline-centered .timeline-entry .timeline-entry-inner .timeline-label.bg-red p {
                color: #ffffff;
            }

            .timeline-centered .timeline-entry .timeline-entry-inner .timeline-label.bg-green {
                /* background: #488c6c; */
                background: #f30051;
            }

            .timeline-centered .timeline-entry .timeline-entry-inner .timeline-label.bg-green:after {
                /* border-color: transparent #488c6c transparent transparent; */
                border-color: transparent #f30051 transparent transparent;
            }

            .timeline-centered .timeline-entry .timeline-entry-inner .timeline-label.bg-green .timeline-title,
            .timeline-centered .timeline-entry .timeline-entry-inner .timeline-label.bg-green p {
                color: #ffffff;
            }

            .timeline-centered .timeline-entry .timeline-entry-inner .timeline-label.bg-orange {
                /* background: #e9662c; */
                background: #808080;
            }

            .timeline-centered .timeline-entry .timeline-entry-inner .timeline-label.bg-orange:after {
                /* border-color: transparent #e9662c transparent transparent; */
                border-color: transparent #808080 transparent transparent;
            }

            .timeline-centered .timeline-entry .timeline-entry-inner .timeline-label.bg-orange .timeline-title,
            .timeline-centered .timeline-entry .timeline-entry-inner .timeline-label.bg-orange p {
                color: #ffffff;
            }

            .timeline-centered .timeline-entry .timeline-entry-inner .timeline-label.bg-yellow {
                background: #f2994b;
            }

            .timeline-centered .timeline-entry .timeline-entry-inner .timeline-label.bg-yellow:after {
                border-color: transparent #f2994b transparent transparent;
            }

            .timeline-centered .timeline-entry .timeline-entry-inner .timeline-label.bg-yellow .timeline-title,
            .timeline-centered .timeline-entry .timeline-entry-inner .timeline-label.bg-yellow p {
                color: #ffffff;
            }

            .timeline-centered .timeline-entry .timeline-entry-inner .timeline-label.bg-blue {
                background: #0a819c;
            }

            .timeline-centered .timeline-entry .timeline-entry-inner .timeline-label.bg-blue:after {
                border-color: transparent #0a819c transparent transparent;
            }

            .timeline-centered .timeline-entry .timeline-entry-inner .timeline-label.bg-blue .timeline-title,
            .timeline-centered .timeline-entry .timeline-entry-inner .timeline-label.bg-blue p {
                color: #ffffff;
            }

            .timeline-centered .timeline-entry .timeline-entry-inner .timeline-label.bg-pink {
                background: #bf3773;
            }

            .timeline-centered .timeline-entry .timeline-entry-inner .timeline-label.bg-pink:after {
                border-color: transparent #bf3773 transparent transparent;
            }

            .timeline-centered .timeline-entry .timeline-entry-inner .timeline-label.bg-pink .timeline-title,
            .timeline-centered .timeline-entry .timeline-entry-inner .timeline-label.bg-pink p {
                color: #ffffff;
            }

            .timeline-centered .timeline-entry .timeline-entry-inner .timeline-label.bg-violet {
                background: #9351ad;
            }

            .timeline-centered .timeline-entry .timeline-entry-inner .timeline-label.bg-violet:after {
                border-color: transparent #9351ad transparent transparent;
            }

            .timeline-centered .timeline-entry .timeline-entry-inner .timeline-label.bg-violet .timeline-title,
            .timeline-centered .timeline-entry .timeline-entry-inner .timeline-label.bg-violet p {
                color: #ffffff;
            }

            .timeline-centered .timeline-entry .timeline-entry-inner .timeline-label.bg-grey {
                background: #4b5d67;
            }

            .timeline-centered .timeline-entry .timeline-entry-inner .timeline-label.bg-grey:after {
                border-color: transparent #4b5d67 transparent transparent;
            }

            .timeline-centered .timeline-entry .timeline-entry-inner .timeline-label.bg-grey .timeline-title,
            .timeline-centered .timeline-entry .timeline-entry-inner .timeline-label.bg-grey p {
                color: #ffffff;
            }

            .timeline-centered .timeline-entry .timeline-entry-inner .timeline-label.bg-dark {
                background: #594857;
            }

            .timeline-centered .timeline-entry .timeline-entry-inner .timeline-label.bg-dark:after {
                border-color: transparent #594857 transparent transparent;
            }

            .timeline-centered .timeline-entry .timeline-entry-inner .timeline-label.bg-dark .timeline-title,
            .timeline-centered .timeline-entry .timeline-entry-inner .timeline-label.bg-dark p {
                color: #ffffff;
            }

            .timeline-centered .timeline-entry .timeline-entry-inner .timeline-label:after {
                content: '';
                display: block;
                position: absolute;
                width: 0;
                height: 0;
                border-style: solid;
                border-width: 9px 9px 9px 0;
                border-color: transparent #ffffff transparent transparent;
                left: 0;
                top: 20px;
                margin-left: -9px;
            }

            .timeline-centered .timeline-entry .timeline-entry-inner .timeline-label .timeline-title,
            .timeline-centered .timeline-entry .timeline-entry-inner .timeline-label p {
                color: #999999;
                margin: 0;
            }

            .timeline-centered .timeline-entry .timeline-entry-inner .timeline-label p+p {
                margin-top: 15px;
            }

            .timeline-centered .timeline-entry .timeline-entry-inner .timeline-label .timeline-title {
                margin-bottom: .5rem;
                font-weight: bold;
            }

            .timeline-centered .timeline-entry .timeline-entry-inner .timeline-label .timeline-title span {
                -webkit-opacity: .6;
                -moz-opacity: .6;
                opacity: .6;
                -ms-filter: alpha(opacity=60);
                filter: alpha(opacity=60);
            }

            .timeline-centered .timeline-entry .timeline-entry-inner .timeline-label p .timeline-img {
                margin: 5px 10px 0 0;
            }

            .timeline-centered .timeline-entry .timeline-entry-inner .timeline-label p .timeline-img.pull-right {
                margin: 5px 0 0 10px;
            }
            .fa{
                color: #fff !important;
            }
        </style>
        <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css" rel="stylesheet">
    @endpush
    <div class="content-wrapper">
        <div class="page-header">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item" aria-current="page"><a
                            href="{{ route('adminSubUser.contract_records.index') }}">list</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Record Details</li>
                </ol>
            </nav>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="row d-flex justify-content-between align-items-center">
                    <div class="row d-flex justify-content-between align-items-center">
                        <div class="mb-3">
                            <h4 style="margin-left: 1.2rem;">Contract History</h4>
                        </div>
                    </div>
                    <div class="text-right d-flex">
                        <div class="text-right">
                        </div>
                    </div>
                </div>
            </div>
        </div>
            <div class="my-5">
                <div class="container bootstrap snippets bootdeys">
                    <div class="col-md-9">
                        <div class="timeline-centered timeline-sm">
                            @forelse ($records as $k => $record)
                            <article class="timeline-entry @if($k%2==1) left-aligned @endif">
                                <div class="timeline-entry-inner">
                                    <time datetime="{!!$record->date_time!!}" class="timeline-time">
                                        <span id='time_{{$record->id}}'>{!!Carbon\Carbon::parse($record->date_time)->format('g:i A')!!}</span>
                                        <span id='date_{{$record->id}}'>{!!dateF($record->date_time)!!}</span>
                                    </time>
                                    <div class="timeline-icon {{$k != 0 ? ($k%2==1 ? 'bg-green' : 'bg-orange') : 'bg-orange' }}"><i class="fa fa-home"></i></div>
                                    <div class="timeline-label {{$k != 0 ? ($k%2==1 ? 'bg-green' : 'bg-orange') : '' }}" style="border-radius: 1rem;"><h4 class="timeline-title">{{ ucwords($record->title) }}</h4>
                                        <p>{{ $record->description }}</p>
                                        @if($record->internal_property_id)
                                            <p style="color:#bf3773;">
                                                <a href='{{route('adminSubUser.property.view', $record?->property?->id)}}' ><u>{{ $record?->property?->title }}</u></a>
                                            </p>
                                        @endif
                                        @if(isset($record->tenant_id) || !empty($record->user_id))
                                            <p class="mt-3">
                                                @php
                                                    $path = $record?->tenant?->image ?? $record?->admin?->image()->first()->path;
                                                    $dummyPath = '/assets/default_user.png';
                                                    $src =  $path ? (Storage::exists($path) ? $path : $dummyPath) : $dummyPath;

                                                    $tenant = $record?->tenant ?? null;
                                                    $offline = ($record?->contract?->offline_tenant) ?? null;
                                                    $string =  $tenant ? ucwords($tenant->name) : ($offline ? ucwords(($offline->first_name ?? '') . ' ' . ($offline->last_name ?? '')) : ($record?->admin ? $record?->admin->name : ''));
                                                @endphp
                                                <img src="{!!Storage::url($src)!!}" alt="" class="timeline-img pull-left rounded-circle" width="40px"  height="40px" style="margin-top:-10px; width:2.2rem; height:2.2rem; object-fit:cover;" onerror="this.onerror=null; this.src='{!!$dummyPath!!}';" loading="lazy">
                                                {{ $string }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            </article>
                            <script type="text/javascript">
                                document.addEventListener('DOMContentLoaded', function() {
                                    const inputDateTime = `{{ $record->date_time }}`;
                                    const isoDateTime = inputDateTime.replace(' ', 'T') + 'Z';
                                    const recordDateTime = new Date(isoDateTime);
                                    const timeElement = document.querySelector('#time_{{$record->id}}');
                                    timeElement.innerHTML = timeF(recordDateTime);
                                });
                            </script>
                            @empty
                            <article class="timeline-entry">
                                <div class="timeline-entry-inner">
                                    No Record Found!
                                </div>
                            </article>
                            @endforelse
                        </div>
                    </div>
            </div>
    </div>
@endsection
