@extends('templates.admin.master')

@section('content')
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">Plans</h6>
                        <a class="font-weight-semibold"
                            href="{{route('admin.plan.create')}}"><i class="fa fa-plus"></i> Create New Plan
                        </a>
                    </div>
                    <div class="ml-2 font-weight-semibold">
                    @include('templates.GlobalTempletes.role_type',['dropDown'=>$company,'filedTitle'=>'Company','name'=>'company_id','apply_col_md'=>true])
                    <button class="btn btn-primary ml-2" id="companyplanfilter">Search</button>
                    </div>

                    
                    <div class="">
                        <table id="plan_table1" class="table datatable-show-all plantablecc">
                            <thead>
                                <tr>
                                    <th>S/No</th>
                                    <th>Name</th>
                                    <th>Plan code</th>
                                    <th>Interest Head Name</th>
                                    <th>Deposit Head Name</th>
                                    <th>Category Name</th>
                                    <th>Minimum Price</th>
                                    <th>Multiple Deposit</th>
                                    <th>Max. Deposit</th>
                                    <th>Effective From</th>
                                    <th>Effective To</th>
                                    <th>Company Name</th>
                                    <th>Status</th>
                                    <th>Created By</th>
                                    <th>Created</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('templates.admin.py-scheme.partials.script')
@stop
