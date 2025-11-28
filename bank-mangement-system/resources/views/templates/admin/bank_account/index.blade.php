@extends('templates.admin.master')

@section('content')
    <div class="content">
        <div class="row">
            
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">Bank Account List</h6>
                        <div class="">
                            <a class="font-weight-semibold" href="{!! route('admin.create.bank_account') !!}"><i class="icon-file-plus mr-2"></i>Bank Account</a>
                        </div>
                    </div>
                    <div class="">
                        <table id="bank_account_listing" class="table datatable-show-all">
                            <thead>
                                <tr>
                                    <th>S/N</th>
                                    <th>Bank Name</th>
                                    <th>Branch Name</th>
                                    <th>Account Number</th>
                                    <th>Ifsc Code</th>   
                                    <th>Address</th>
                                    <th>Status</th>
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
    @include('templates.admin.bank_account.partials.script')
@stop
