@extends('templates.admin.master')

@section('content')

@if(session('success'))
<div class="alert alert-success">
    {{ session('success') }}
</div>
@endif

@if(session('error'))
<div class="alert alert-danger">
    {{ session('error') }}
</div>
@endif


<div class="content">

    <div class="row">

        <div class="col-lg-12">

            @if ($errors->any())

            <div class="col-md-12">

                <div class="alert alert-danger">

                    <ul>

                        @foreach ($errors->all() as $error)

                        <li>{{ $error }}</li>

                        @endforeach

                    </ul>

                </div>

            </div>

            @endif



            <div class="row">



                <div class="col-lg-12">

                    <div class="card bg-white">

                        <div class="card-body">

                            <h3 class="card-title mb-3 maintital">Advance Payment List</h3>


                        </div>

                    </div>

                </div>

                <!-- Fillters -->
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header header-elements-inline">
                            <h6 class="card-title font-weight-semibold">Search Filter</h6>
                        </div>
                        <div class="card-body">
                            <form action="#" method="post" enctype="multipart/form-data" id="filter" name="filter">
                                @csrf
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-12">Start Date </label>
                                            <div class="col-lg-12 error-msg">
                                                <div class="input-group">
                                                    <input type="text" class="form-control  " name="start_date" id="start_date" autocomplete="off">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-12">End Date </label>
                                            <div class="col-lg-12 error-msg">
                                                <div class="input-group">
                                                    <input type="text" class="form-control  " name="end_date" id="end_date" autocomplete="off">
                                                </div>
                                            </div>
                                        </div>
                                    </div>


                                    @php
                                    $dropDown = $AllCompany;
                                    $filedTitle = 'Company';
                                    $name = 'company_id';
                                    @endphp
                                    @include('templates.GlobalTempletes.both_company_filter_new',['all'=>true])


                                    <div class="col-md-4">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-12">Advance type</label>
                                            <div class="col-lg-12 error-msg">
                                                <select name="paymentType" id="paymentType" class="form-control" aria-invalid="false">

                                                    <option value="">Please Select</option>

                                                    <option data-val="0" value="0">Advance Rent</option>

                                                    <option data-val="1" value="1">Advance Salary</option>

                                                    <option data-val="2" value="2">TA advanced/Imprest</option>

                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-12">Settlement Status</label>
                                            <div class="col-lg-12 error-msg">
                                                <select name="settlement" id="settlement" class="form-control">

                                                    <option value="">Please Select</option>

                                                    <option value="1">Fully Settled</option>

                                                    <option value="0">Pending</option>

                                                    <option value="2">Partially Settled</option>

                                                </select>
                                            </div>
                                        </div>
                                    </div>


                                    <div class="col-md-12">
                                        <div class="form-group row">
                                            <div class="col-lg-12 text-right">
                                                <input type="hidden" name="is_search" id="is_search" value="yes">
                                                <input type="hidden" name="payment_export" id="payment_export" value="">
                                                <button type="button" class=" btn bg-dark legitRipple" onClick="searchForm()">Submit</button>
                                                <button type="button" class="btn btn-gray legitRipple" id="reset_form" onClick="resetForm()">Reset </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>


                <!-- Listings -->
                <div class="card bg-white col-lg-12 flisting" style="display:none;">

                    <div class="card-header header-elements-inline">
                        <h6>Advance Payment List </h6>
                        <div class="">
                            <button type="button" class="btn bg-dark legitRipple payment_export ml-2" data-extension="0" style="float: right;">Export xslx</button>
                            <!-- <button type="button" class="btn bg-dark legitRipple export" data-extension="1">Export PDF</button>-->
                        </div>
                        <input type="hidden" class="form-control created_at " name="created_at" id="created_at">
                        <input type="hidden" class="form-control create_application_date " name="create_application_date" id="create_application_date">
                    </div>


                    <table id="Advance_request" class="table datatable-show-all">
                        <thead>
                            <tr>
                                <th>S/N</th>
                                <th>Branch Name</th>
                                <th>Advance Date</th>
                                <th>Type</th>
                                <th>Name</th>
                                <th>Advance Amount</th>
                                <th>Description</th>
                                <th>Settled Amount</th>
                                <th>Return/Excess Amount</th>
                                <th>Settlement Status</th>
                                <th>Company Name</th>
                                <th>User</th>
                                <th class="text-center">Action</th>
                                <!-- <th>Branch Code</th>
                                <th>SubType</th> -->
                            </tr>
                        </thead>
                    </table>
                </div>

            </div>

        </div>

    </div>

    <!-- Reject Model -->

    <div class="modal fade" id="remark" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title float-start">Reject Adavance Payment</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="remarkform" action="" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="container form-group mt-2">
                        <input class="form-control" type="text" name="remark" placeholder="Remark">
                        <input type="hidden" name="created_at" class="created_at" id="created_at">
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Save changes</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <script>
        // set the url value in remark form

        $(document).ready(function() {
            $(document).on("click", ".remark", function() {
                const url = $(this).data('url');
                const id = $(this).data('id');
                $('#remarkform').attr('action', url);
            });
        });
    </script>



    @include('templates.admin.AdvancePayment.partials.payment_script_list')

    @stop