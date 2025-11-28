@extends('layouts/branch.dashboard')

@section('content')

<div class="container-fluid mt--6">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-lg-12">
                <div class="card bg-white">
                <div class="card-body page-title"> 
                        <h3 class="">Employee Ledger</h3> 
                        <a href="{!! route('branch.hr.employee_list') !!}" style="float:right" class="btn btn-secondary">Back</a>
                    
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
                                <h3 class="mb-0 text-dark">{{ $detail->Company->name }} - {{$detail->employee_name}}  - Ledger</h3>
                            </div>
                            <!-- <div class="col-md-4 text-right">

                                <button type="button" class="btn btn-primary legitRipple export ml-2" data-extension="0" style="float: right;">Export xslx</button>
                            </div> -->
                            </div>
                            <form action="#" method="post" enctype="multipart/form-data" id="filter" name="filter">
                        @csrf

                        <input type="hidden" name="liability" id="liability" value="{{$emp}}"> 
                        </form>
                        </div>
                    
                    <div class="table-responsive">
                        <table class="table datatable-show-all" id="emp_ledger">
                        <thead>
                            <tr>                                    
                                        <th >S.No</th> 
                                        <!-- <th> Company Name </th>  -->
                                        <th >Date</th> 
                                        <th >Description</th>
                                        <th >Reference No</th>  
                                        <!-- <th >Withdrawal</th>    -->
                                        <th >Deposit</th>
                                        <!-- <th >Opening Balance</th> -->
                                        <th >Payment Mode</th> 
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
@include('templates.branch.hr_management.employee.emp_ledger_script')
@stop