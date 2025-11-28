@extends('layouts/branch.dashboard')

@section('content')

<div class="container-fluid mt--6">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-lg-12">
                <div class="card bg-white">
                <div class="card-body page-title">
                    
                        <h3 class="">{{ $memberDetail->first_name }} {{ $memberDetail->last_name }}({{ $memberDetail->member_id}}) - Loan Detail</h3>
                        <a href="{!! route('branch.member_loanlist',['id'=>$memberDetail->id]) !!}" style="float:right" class="btn btn-secondary">Back</a>
                    
                </div>
                </div>
            </div>
        </div>
        <div class="row">  
            <div class="col-lg-12">                

                <div class="card bg-white shadow">
                    <div class="card-header bg-transparent">
                        <h3 class="mb-0 text-dark">Loan - {{ $loanDetail->loan->name }}</h3>
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
@include('templates.branch.loan_management.partials.listing_js')
@stop