@extends('layouts/branch.dashboard')
@section('content')

@include('templates.branch.events.css')
<style>
    .fc-row.fc-rigid .fc-content-skeleton, .fc-row table { height:100%; }
    .fc-row .fc-content-skeleton td, .fc-row .fc-helper-skeleton td { vertical-align: middle;}
    .fc-row .fc-content-skeleton td a.fc-day-grid-event {  margin:1px 5px; margin-top: -30px!important; }
    
    .fc-row .fc-content-skeleton td a.fc-day-grid-event.orange { background: #ffa500!important; border-color: #ffa500!important;} 
    .fc-row .fc-content-skeleton td a.fc-day-grid-event.green { background: #008000!important; border-color: #008000!important;} 
    .fc-row .fc-content-skeleton td a.fc-day-grid-event.red { background: #f05050!important; border-color: #f05050!important;} 
    .fc-row .fc-content-skeleton td a.fc-day-grid-event.dark-red { background: #d31313!important; border-color: #d31313!important;} 
</style>
<div class="container-fluid mt--6">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-lg-12">
                <div class="card bg-white">
                <div class="card-body">
                    <div class="">
                        <h3 class="">Passbook</h3> 
                    </div>
                </div>
                </div>
            </div>
        </div>
        <div class="row">  
            <div class="col-lg-12">
                <div class="card bg-white shadow">
                    <div class="card-header bg-transparent">
                        <h3 class="mb-0 text-dark">Holidays Calendar</h3>
                    </div>
                    <div class="card-body">
                        <div class="col-lg-12"> 
                            <div class="panel-body">
                                {!! $calendar->calendar() !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div> 
        </div>
         
    </div>
@endsection

@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.9.0/moment.min.js"></script>
@include('templates.branch.events.js')
{!! $calendar->script() !!}
@include('templates.branch.events.partials.script')
@endsection