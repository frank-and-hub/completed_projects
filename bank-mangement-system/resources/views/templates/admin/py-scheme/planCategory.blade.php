@extends('templates.admin.master')

@section('content')

    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">Categories</h6>
                        <a href="{{route('add_category')}}"><i class="fa fa-plus"></i> Create New Category</a>
                    </div>
                    <div class="">
                        <table id="planCategory_table" class="table datatable-show-all">
                            <thead>
                                <tr>
                                    <th>S. No.</th>
                                    <th>Name</th>
                                    <th>Category code</th>                                              
                                    <th>Basic</th>
                                    <th>Status</th>
                                    <th>Created By</th>
                                    <th>Created At</th>
                                    <th class="text-center">Action</th>    
                                </tr>
                            </thead>                    
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@include('templates.admin.py-scheme.partials.planCategoryScript')
@stop