@extends('templates.admin.master')

@section('content')
    <div class="content">
        <div class="row">
            
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">Eli Loan List</h6>
                        <div class="">
                           <a class="font-weight-semibold" href="{!! route('admin.create.eli-loan') !!}"><i class="icon-file-plus mr-2"></i>Eli Loan</a>
                        </div>
                    </div>
                    <div class="">
                        <table id="eli_loan_listing" class="table datatable-show-all">
                            <thead>
                                <tr>
                                    <th>S/N</th>
                                    <th>Head Name</th>
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
    @include('templates.admin.eli_loan.partials.script')
@stop