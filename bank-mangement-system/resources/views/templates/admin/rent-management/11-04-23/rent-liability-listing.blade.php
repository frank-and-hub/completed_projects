@extends('templates.admin.master')
@section('content')
@section('css')
<style>
    .hideTableData {
        display: none;
    }
</style>
@endsection
    <div class="content">

        <div class="row">

            <div class="col-md-12">

                <div class="card">

                    <div class="card-header header-elements-inline">

                        <h6 class="card-title font-weight-semibold">Search Filter</h6>

                    </div>

                    <div class="card-body">

                        <form action="#" method="post" enctype="multipart/form-data" id="filter" name="filter">

                        @csrf

                            <div class="row">



                               @if(Auth::user()->branch_id<1)

                                <div class="col-md-4">

                                    <div class="form-group row">

                                        <label class="col-form-label col-lg-12">Select Branch </label>

                                        <div class="col-lg-12 error-msg">

                                            <div class="input-group">

                                                <select class="form-control" id="filter_branch" name="filter_branch">

                                                    <option value=""  >----Select----</option> 

                                                    @foreach(  $branches as  $val )

                                                    <option value="{{ $val->id }}"  >{{ $val->name }}</option> 

                                                    @endforeach

                                                </select>

                                               </div>

                                        </div>

                                    </div>

                                </div>

                                @else

                                  <input type="hidden" name="branch_id" id="branch_id" value="{{Auth::user()->branch_id}}">                         

                                @endif



                                <div class="col-md-4">

                                    <div class="form-group row">

                                        <label class="col-form-label col-lg-12">Rent Type </label>

                                        <div class="col-lg-12 error-msg">

                                            <div class="input-group">

                                                <select class="form-control" id="rent_type" name="rent_type">

                                                    <option value=""  >----Select----</option> 

                                                    @foreach( $accountHeadLibilities as  $val )

                                                    <option value="{{ $val->id }}"  >{{ $val->sub_head }}</option> 

                                                    @endforeach

                                                </select>

                                               </div>

                                        </div>

                                    </div>

                                </div>



                                <div class="col-md-12">

                                    <div class="form-group row"> 

                                        <div class="col-lg-12 text-right" >

                                            <input type="hidden" name="is_search" id="is_search" value="no">

                                            <input type="hidden" name="loan_recovery_export" id="loan_recovery_export" value="">

                                            <button type="button" class=" btn bg-dark legitRipple" onClick="searchForm()" >Submit</button>

                                            <button type="button" class="btn btn-gray legitRipple" id="reset_form" onClick="resetForm()" >Reset </button>

                                        </div>

                                    </div>

                                </div>

                            </div>

                        </form>

                    </div>

                </div>

            </div>

            <div class="col-md-12 table-section hideTableData">

                <div class="card">

                    <div class="card-header header-elements-inline">

                        <h6 class="card-title font-weight-semibold">Rent Liabilities</h6>

                        <div class="">
                            <button type="button" class=" btn bg-dark legitRipple export ml-2" style="float: right;">Export xslx</button>
                            <!-- <a type="button" class="btn bg-dark legitRipple export ml-2" href="{{url('admin/exportrentliabilities')}}" ></a> -->
                        </div>

                    </div>

                    <table class="table datatable-show-all" id="rent-liabilities-table">

                        <thead>

                            <tr>

                                <th width="5%">S/N</th>

                                <th>BR Name</th>

                                <th>BR Code</th>

                                <th>SO Name</th>

                                <th>RO Name</th>

                                <th>ZO Name</th>

                                <th width="10%">Rent Type</th>

                                <th width="10%">Period From</th>

                                <th width="10%">Period To</th>

                                <th width="5%">Address</th>

                                <th width="5%">Owner Name</th>

                                <th width="5%">Owner Mobile Number </th>

                                <th width="5%">Owner Pan Card</th>

                                <th width="10%">Owner Aadhar Card</th>

                                <th width="10%">Owner SSB account</th>

                                <th width="10%">Owner Bank name</th>

                                <th width="10%">Owner Bank account Number</th>

                                <th width="10%">Owner IFSC code</th>

                                <th width="10%">Security amount </th>

                                <th width="10%">Rent</th>

                                <th width="10%">Yearly Increment</th>

                                <th width="10%">Office Square feet area</th>

                                <th width="10%">Employee Code</th>

                                <th width="10%">Authorized Employee name</th>

                                <th width="10%">Authorized Employee Designation</th>

                                <th width="10%">Mobile No.</th>

                                <th width="10%">Rent Agreement</th>

                                <th width="10%">Agreement Status</th>
                                <th width="10%">Created Date</th>

                                <th width="10%">Action</th>

                            </tr>

                        </thead>

                    </table>

                </div>

            </div>

        </div>

    </div>

@stop



@section('script')

<script src="{{url('/')}}/asset/js/sweetalert.min.js"></script>

@include('templates.admin.rent-management.partials.script')

@endsection

