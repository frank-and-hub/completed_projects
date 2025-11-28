@extends('layouts/branch.dashboard')

@section('content')

<div class="container-fluid mt--6">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-lg-12">
                <div class="card bg-white">
                <div class="card-body">
                    <div class="">
                        <h3 class="">Update Investment Date</h3>
                    </div>
                </div>
                </div>
            </div>
        </div>

        <div class="row">  
            <div class="col-lg-12">                

                <div class="card bg-white shadow">
                    <div class="card-header bg-transparent">
                        <div class="row">
                            <div class="col-md-8">
                                <h3 class="mb-0 text-dark">Investments</h3>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <form action="{{route('updatedate')}}" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <!-- <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Account Number</label>
                                    <div class="col-lg-12 error-msg">
                                        <div class="input-group">
                                            <input type="text" class="form-control" name="acc_n" id="acc_n"  >
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Amount</label>
                                    <div class="col-lg-12 error-msg">
                                        <div class="input-group">
                                            <input type="text" class="form-control amount" name="amount" id="amount">
                                        </div>
                                    </div>
                                </div>
                            </div> -->
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Last Date</label>
                                    <div class="col-lg-12 error-msg">
                                        <div class="input-group">
                                            <input type="date" class="form-control start_date" name="lastdate" id="lastdate">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">New Date</label>
                                    <div class="col-lg-12 error-msg">
                                        <div class="input-group">
                                            <input type="date" class="form-control end_date" name="newdate" id="newdate"  >
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group text-right">
                                    <div class="col-lg-12 page">
                                        <input type="Submit" class=" btn btn-primary legitRipple" name="Submit" value="Submit">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('script')
@include('templates.branch.investment_management.partials.script')
@stop
