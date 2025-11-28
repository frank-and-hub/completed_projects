@extends('templates.admin.master')

@section('content')
    <div class="content">
        <div class="row">
            <!-- <div class="col-md-12">
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">Saving Account Listing</h6>
                    </div>
                </div>
            </div>  -->
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">{{$memberDetail->first_name}} {{$memberDetail->last_name}}   ({{$memberDetail->member_id}}) - Saving Account Listing</h6>
                    </div>
                    <div class="">
                        <table id="member_Saving" class="table datatable-show-all">
                            <thead>
                                <tr>
                                    <th>S/N</th>
                                    <th>Company Name</th>
                                    <th>Customer ID</th>
                                    <th>Member ID</th>                                 
                                     <th>Account Number</th>
                                    <th>Member Name</th>
                                    <th>Balance </th>
                                    <th class="text-center">Action</th>    
                                </tr>
                            </thead>                    
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
   
@include('templates.admin.member.partials.listing_saving')
@stop