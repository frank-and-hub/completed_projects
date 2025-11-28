@extends('templates.admin.master')

@section('content')
<div class="content">
    <div class="row">  
        <div class="col-lg-12">                
            <div class="card bg-white shadow">
                <div class="card-header bg-transparent">
                    <h3 class="mb-0 text-dark">Investments - {{ $investment->plan->name }}</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-12"> 
                            In progress
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('script')
@include('templates.admin.investment_management.partials.listing_js')
@stop