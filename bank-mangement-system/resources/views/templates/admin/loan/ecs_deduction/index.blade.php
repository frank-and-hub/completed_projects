@extends('templates.admin.master')

@section('content')
<div class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header header-elements-inline">
                    <h6 class="card-title font-weight-semibold">Search Filter</h6>
                </div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data" name='searchForm' id='searchForm'
                        class='searchForm'>
                        @csrf
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Loan Type <sup class="error">*</sup></label>
                                    <div class="col-lg-12 error-msg">
                                        <select class="form-control" id="loan_type" name="loan_type">
                                            <option value="">Select loan type</option>
                                            <option value="all">All</option>
                                            <option value="L">Loan</option>
                                            <option value="G">Group Loan</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">ECS Type</label>
                                    <div class="col-lg-12 error-msg">
                                        <select class="form-control" id="ecs_type" name="ecs_type">
                                            <option value="">Select ECS Type</option>
                                            <option value="1">Bank</option>
                                            <option value="2">SSB</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                             <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Company</label>
                                    <div class="col-lg-12 error-msg">
                                        <select class="form-control " name="company_id" id="company_id" title="Please Select Company" required="">
                                            <option value="">Select Company</option>
                                            <option value="0">All Company</option>   
                                            <option value="1">SAMRADDH BESTWIN MICRO FINANCE ASSOCIATION</option>
                                            <option value="2">ROYAL RAO BALAJI MICRO FINANCE FOUNDATION</option>
                                            <option value="3">UJALA MICRO FINANCE</option>
                                    </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">From Date</label>
                                    <div class="col-lg-12 error-msg">
                                        <input type="text" class="form-control" name="emi_due_date" id="emi_due_date" placeholder="Select from date" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">To Date</label>
                                    <div class="col-lg-12 error-msg">
                                        <input type="text" class="form-control" name="emi_due_to_date" id="emi_due_to_date" placeholder="Select to date" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group row">
                                    <div class="col-lg-12 text-right">
                                        <input type="hidden" name="is_search" id="is_search" value="no">
                                        <input type="hidden" name="ecs_deduction_export" id="ecs_deduction_export" value="">
                                        <input type="hidden" class="create_application_date" name="create_application_date" id="create_application_date" >
                                        <button type="button" class=" btn bg-dark legitRipple"
                                            id="search">Submit</button>
                                        <button type="button" class="btn btn-gray legitRipple" id="reset_form"
                                            >Reset </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card bg-white shadow data_div d-none">
                <div class="card-header bg-transparent header-elements-inline">
                    <h3 class="mb-0 text-dark">ECS Deduction Listing</h3>
                    <div class="">
                        <button type="button" class="btn btn-dark bankExport" data-extensions="1">Bank Export</button>
                        <button type="button" class="btn btn-dark export" data-extension="0">Export XSLX</button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="ecs_deduction" class="table datatable-show-all">
                        <thead>
                            <tr>
                                <th>S/N</th>
                                <th>Region</th>
                                <th>Branch Name</th>
                                <th>Customer Name</th>
                                <th>Loan Account No</th>
                                <th>Plan</th>
                                <th>Loan Amount</th>
                                <th>Sanction Date</th>
                                <th>Emi Amount</th>
                                <th>Emi Due Date</th>
                                <th>Mode</th>
                                <th>Mobile No</th>
                                <th>ECS Reference No</th>
                                <th>ECS Type</th>
                                {{-- <th>Customer Mobile No</th>
                                <th>Associate Code</th>
                                <th>Associate Name</th> --}}
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@include('templates.admin.loan.ecs_deduction.partials.script')
@stop

