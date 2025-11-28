@extends('layouts/branch.dashboard')



@section('content')



<div class="container-fluid mt--6">

    <div class="content-wrapper">

        <div class="row">

            <div class="col-lg-12">

                <div class="card bg-white">

                <div class="card-body">

                    <div class="">

                        <h3 class="">SSB Deposit List</h3>

                    </div>

                </div>

                </div>

            </div>

        </div>



        <div class="row">

            <div class="col-md-12">

            <div class="card">

                <div class="card-header header-elements-inline">

                    <h3 class="card-title font-weight-semibold">Search Filter</h3>

                </div>

                <div class="card-body">

                    <form action="#" method="post" enctype="multipart/form-data" id="filter" name="filter">

                        @csrf

                        <div class="row">

                            <div class="col-md-4">

                                <div class="form-group row">

                                    <label class="col-form-label col-lg-12">From Date</label>

                                    <div class="col-lg-12 error-msg">

                                        <div class="input-group">

                                            <input type="text" class="form-control  " name="start_date" id="start_date"  >

                                        </div>

                                    </div>

                                </div>

                            </div>

                            <div class="col-md-4">

                                <div class="form-group row">

                                    <label class="col-form-label col-lg-12">To Date</label>

                                    <div class="col-lg-12 error-msg">

                                        <div class="input-group">

                                            <input type="text" class="form-control" name="end_date" id="end_date"  >

                                        </div>

                                    </div>

                                </div>

                            </div>

                            

                            

                            <div class="col-md-4">

                                    <div class="form-group row">

                                        <label class="col-form-label col-lg-12">Associate Code </label>

                                        <div class="col-lg-12 error-msg">

                                            <input type="text" name="associate_code" id="associate_code" class="form-control"  > 

                                        </div>

                                    </div>

                                </div>

                            

							

                            <div class="col-md-12">

                                <div class="form-group text-right">

                                    <div class="col-lg-12 page">

                                        <input type="hidden" name="is_search" id="is_search" value="no">

                                        <input type="hidden" name="investments_export" id="investments_export" value="">

                                        <button type="button" class=" btn btn-primary legitRipple investment_filters" onClick="searchForm()" >Submit</button>

                                        <button type="button" class="btn btn-gray legitRipple" id="reset_form" onClick="resetForm()" >Reset </button>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </form>

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

                            <h3 class="mb-0 text-dark">SSB Deposit List</h3>

                        </div>

                            

                        </div>

                    </div>

                    <div class="table-responsive">

                         <table id="SavingAccountReport-listing" class="table table-flush">

                       <thead class="">

                            <tr>

                            <th>S/N</th>

                            <th>Created Date</th>

                            <th>BR Name</th>

                            <th>BR Code</th>

                            <th>SO Name</th>

                            <th>RO Name</th>  

                            <th>ZO Name</th>

                            <th>Member ID</th>

                            <th>Account Number</th>

                            <th>Member(Account Holder Name)</th> 

                            <!--<th>Plan</th>

                            <th>Tenure</th>-->

                            <th>Amount</th>

                            <th>Associate Code</th>

                            <th>Associate Name</th>

                             <th>Payment Mode</th>

                            

                            </tr>

                        </thead>

                    </table>

                    </div>

                </div>

            </div>

        </div>

    </div>

@stop



@section('script')

@include('templates.branch.investment_management.partials.savingaccountreport_script')

@stop

