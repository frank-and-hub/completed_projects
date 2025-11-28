@extends('templates.admin.master')

@section('content')
<div class="content">
    <div class="row">  
        <div class="col-lg-12">                
            <div class="card bg-white shadow">
                <div class="card-header bg-transparent">
                    <h3 class="mb-0 text-dark">Investments</h3>
                </div>
                <div class="table-responsive">
                    <table id="member_investment_listing" class="table table-flush">
                        <thead class="">
                          <tr>
                            <th>S/N</th>
                            <th>Date</th>
                            <th>Plan</th>
                            <th>Amount</th>
                            <th>Tenure</th>
                            <th>Action</th>
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
@include('templates.admin.investment_management.partials.listing_js')
@stop