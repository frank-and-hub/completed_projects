@extends('templates.admin.master')

@section('content')
@section('css')
    <style>
        .hideTableData {
            display: none;
        }
    </style>
@endsection
@php
    $dropDown = $company;
    $filedTitle = 'Company';
    $name = 'company_idd';
@endphp
<div class="content">
    <div class="row ">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header header-elements-inline">
                    <h6 class="card-title font-weight-semibold">Search Filter</h6>
                </div>
                <div class="card-body">
                    <form action="#" method="post" enctype="multipart/form-data" id="loan-filter" class="loan_filter"
                        name="loan-filter">
                        @csrf
                        <input type="hidden" name="create_application_date" class="create_application_date"
                            id="create_application_date">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Date From</label>
                                    <div class="col-lg-12 error-msg">
                                        <div class="input-group">
                                            <input type="text" readonly class="form-control from_date" name="date_from"
                                                autocomplete="off">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Date To</label>
                                    <div class="col-lg-12 error-msg">
                                        <div class="input-group">
                                            <input type="text" readonly class="form-control to_date" name="date_to"
                                                autocomplete="off">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @include('templates.GlobalTempletes.role_type', [
                                'dropDown' => $dropDown,
                                'filedTitle' => $filedTitle,
                                'name' => $name,
                                'value' => '',
                                'multiselect' => 'false',
                                'apply_col_md' => true,
                                'classes' => 'findBranh',
                            ])


                            @if (Auth::user()->branch_id < 1)
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Select Branch </label>
                                        <div class="col-lg-12 error-msg">
                                            <div class="input-group">
                                                <select class="form-control" id="branch_id" name="branch_id">
                                                    <option value="">----Select----</option>

                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <input type="hidden" name="branch_id" id="branch_id"
                                    value="{{ Auth::user()->branch_id }}">
                            @endif
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Account Number </label>
                                    <div class="col-lg-12 error-msg">
                                        <input type="text" class="form-control  " name="application_number"
                                            id="application_number">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Member Name </label>
                                    <div class="col-lg-12 error-msg">
                                        <input type="text" class="form-control  " name="member_name"
                                            id="member_name">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Member ID </label>
                                    <div class="col-lg-12 error-msg">
                                        <input type="text" name="member_id" id="member_id" class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Customer ID </label>
                                    <div class="col-lg-12 error-msg">
                                        <input type="text" name="customer_id" id="customer_id" class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Associate code </label>
                                    <div class="col-lg-12 error-msg">
                                        <input type="text" name="associate_code" id="associate_code"
                                            class="form-control">
                                    </div>
                                </div>
                            </div>
                            <!------------------ loan Plan Dynamic  --->
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Loan Type<span class="required">*</span></label>
                                    <div class="col-lg-12 error-msg">
                                        {{-- <div class="input-group"> --}}
                                            <select class="form-control" id="loan_type" name="loan_type">
                                                <option value="">----Select----</option>
                                                <option value="L">Loan</option>
                                                <option value="G">Group Loan</option>
                                            </select>
                                        {{-- </div> --}}
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Loan Plan </label>
                                    <div class="col-lg-12 error-msg">
                                        <div class="input-group">
                                            <select class="form-control" id="loan_plan" name="loan_plan">
                                                <option value="">----Select----</option>

                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 group_loan_common d-none">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Group Leader ID </label>
                                    <div class="col-lg-12 error-msg">
                                        <input type="text" name="group_loan_common_id" id="group_loan_common_id"
                                            class="form-control">
                                    </div>
                                </div>
                            </div>
                            <!------------------ loan Plan Dynamic  --->
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Status </label>
                                    <div class="col-lg-12 error-msg">
                                        <select class="form-control" id="status" name="status">
                                            <option value="">Select status</option>
                                            <option value="0">Pending</option>
                                            <option value="1">Approved</option>
                                            <option value="3">Clear</option>
                                            <option value="4">Due</option>
                                            <option value="5">Rejected</option>
                                            <option value="6">Hold</option>
                                            <option value="7">Approved but Hold</option>
                                            <option value="8">Cancel</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group row">
                                    <div class="col-lg-12 text-right">
                                        <input type="hidden" name="company_id" id="company_id">
                                        <input type="hidden" name="is_search" id="is_search" value="no">
                                        <input type="hidden" name="loan_details_export" id="loan_details_export"
                                            value="">
                                        <button type="submit" class=" btn bg-dark legitRipple">Submit</button>
                                        <button type="button" class="btn btn-gray legitRipple" id="reset_form">Reset
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-12 table-section d-none">
            <div class="card">
                <div class="card-header header-elements-inline">
                    <h6 class="card-title font-weight-semibold">Loan Registration Details</h6>
                    <div class="">
                        <button type="button" class="btn bg-dark legitRipple export-loan ml-2" data-extension="0"
                            style="float: right;">Export xslx</button>
                        {{-- <button type="button" class="btn bg-dark legitRipple export-loan" data-extension="1">Export
                            PDF</button> --}}
                    </div>
                </div>
                <div>
                    <table id="loan_request_table" class="table datatable-show-all">
                        <thead>
                            <tr>
                                <th>S/N</th>
                                <th>Loan Plan</th>
                                <th>Loan Tenure</th>
                                <th>Emi Option</th>
                                <th>Applicant/Group Leader Id</th>
                                <th>Application Number</th>
                                <th>Company</th>
                                <th>Branch </th>
                                <th>A/C No.</th>
                                <th>Member Id</th>
                                <th>Customer Id</th>
                                <th>Member Name</th>
                                <th>Total Deposit Amt</th>
                                <th>Last Recovery Date</th>
                                <th>Associate Code</th>
                                <th>Associate Name</th>
                                <th>Transfer Amount</th>
                                <th>Transfer Date</th>
                                <th>Loan Amount</th>
                                <th>File Charge Amount</th>
                                <th>ECS Reference </th>
                                <th>ECS Charge Amount</th>

                                <th>Igst ECS Charges</th>
                                <th>Cgst ECS Charges</th>
                                <th>Sgst ECS Charges</th>
                                <th>Igst File Charges</th>
                                <th>Cgst File Charges</th>
                                <th>Sgst File Charges</th>
                                <th>Insurance Charges</th>
                                <th>Igst Insurance Charges</th>
                                <th>Cgst Insurance Charges</th>
                                <th>Sgst Insurance Charges</th>
                                <th>Bank Name</th>
                                <th>Bank Account Number</th>
                                <th>IFSC Code</th>
                                <th>Reason</th>
                                <th>Status</th>
                                <th>Approved Date</th>
                                <th>Application Date</th>
                                <th>Running Loan Account Number</th>
								<th>Running Loan Closing Amount</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@include('templates.admin.modal.index')
<div class="modal fade" id="exampleModal" class="refModel" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <form action="#" method="post" enctype="multipart/form-data" id="ecsRef" class="" name="ecsRef">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">ECS Register</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">

                    <div class="form-group">
                        <label for="message-text" class="col-form-label">Enter Reference Code</label>
                        <input type="text" class="form-control" id="ref-text" name="ref-text">
                        <input type="hidden" class="form-control" id="ref_id" name="refId">
                        <input type="hidden" class="form-control" id="old_val" name="oldVal">

                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary escRefsubmit">Submit</button>
                </div>
                </div>
            </div>
        </form>
    </div>
<!-- <div class="modal fade" id="loan-rejected" tabindex="-1" role="dialog" aria-labelledby="modal-form" aria-hidden="true">
  <div class="modal-dialog modal- modal-dialog-centered modal-sm" role="document" style="max-width: 600px !important; ">
    <div class="modal-content">
      <div class="modal-body p-0">
        <div class="card bg-white border-0 mb-0">
          <div class="card-header bg-transparent pb-2ß">
            <div class="text-dark text-center mt-2 mb-3">Reject Loan Request</div>
          </div>
          <div class="card-body px-lg-5 py-lg-5">
            <form action="" method="post" id="loan-reject-form" name="loan-reject-form">
              @csrf
              <input type="hidden" name="loan_id" id="loan_id" value="">
              <div class="form-group row">
                <div class="col-lg-12">
                  <textarea name="rejection" name="rejection" rows="6" cols="50" class="form-control"
                      placeholder="Remark"></textarea>
                </div>
              </div>

              <div class="text-right">
                <input type="submit" name="submitform" value="Submit" class="btn btn-primary">
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div> -->
@stop

@section('script')
<script src="{{ url('/') }}/asset/js/sweetalert.min.js"></script>
@include('templates.admin.loan.partials.script')
<script>
    $(document).ready(function() {

        $('.findBranh').change(function(e) {

            $('#branch_code').val('');
            e.preventDefault();
            var companyId = $(this).val();
            $('#company_id').val(companyId);
            $.ajax({
                type: "POST",
                url: "{{ route('admin.fetchbranchbycompanyid') }}",
                data: {
                    'company_id': companyId,
                    'branch': 'true',
                    'bank': 'false',
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    let myObj = JSON.parse(response);
                    console.log(myObj);
                    if (myObj.branch) {
                        var optionBranch =
                            `<option value="">----Select----</option>`;
                        myObj.branch.forEach(element => {
                            optionBranch +=
                                `<option value="${element.id}"  data-value="${element.branch_code}">${element.name}</option>`;
                        });
                        $('#branch_id').html(optionBranch);

                    }
                    if (myObj.bank) {
                        var optionBank = `<option value="">----Please Select----</option>`;
                        myObj.bank.forEach(element => {
                            optionBank +=
                                `<option value="${element.id}">${element.bank_name}</option>`;
                        });
                        $('#bank').html(optionBank);
                    }
                }
            });
        });

        //Get Loan type onchange get
        $('#loan_type').on('change', function() {
            var company_id = $('#company_idd').val();
            var loanType = $('#loan_type').val();
            if (company_id == "") {
                $(this).val('');
                swal('Warning!', 'Please select the company first');
                return false;
            }
            if ($(this).val() == "") {
                return false;
            }
            $.ajax({
                type: "POST",
                url: "{{ route('admin.loan.getplanlist') }}",
                dataType: 'JSON',
                data: {
                    'loan_type': loanType,
                    'company_id': company_id,

                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    let html = `<option value="">----Select----</option>`;
                    if (resetForm != "") {
                        response.forEach(element => {
                            html +=
                                `<option value='${element.id}'>${element.name } ( ${element.code} )</option>`;
                        });
                        $("#loan_plan").html(html);
                    }

                }
            })
            if (loanType != 'G') {
                $('.group_loan_common').hide();
                $('.group_loan_common').val('');
            } else(
                $('.group_loan_common').show().removeClass('d-none')
            )
        })
        loanRequestTable = $('#loan_request_table').DataTable({
            processing: true,
            serverSide: true,
            // bFilter: false,
            // ordering: false,
            pageLength: 20,
            lengthMenu: [20, 40, 50, 100],
            "fnRowCallback": function(nRow, aData, iDisplayIndex) {
                var oSettings = this.fnSettings();
                $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart + iDisplayIndex + 1);
                return nRow;
            },
            ajax: {
                "url": "{!! route('admin.loan.requestlist') !!}",
                "type": "POST",
                "data": function(d) {
                    d.searchform = $('.loan_filter').serializeArray()
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
            },
            columns: [{
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex'
                },
                {
                    data: 'loan',
                    name: 'loan'
                },
                {
                    data: 'emi_period',
                    name: 'emi_period'
                },
                {
                    data: 'emi_option',
                    name: 'emi_option'
                },
                {
                    data: 'group_loan_id',
                    name: 'group_loan_id'
                },
                {
                    data: 'applicant_id',
                    name: 'applicant_id'
                },
                {
                    data: 'company',
                    name: 'company'
                },
                {
                    data: 'branch',
                    name: 'branch'
                },
                {
                    data: 'application_number',
                    name: 'application_number'
                },
                {
                    data: 'member_id',
                    name: 'member_id'
                },
                {
                    data: 'customer_id',
                    name: 'customer_id'
                },
                {
                    data: 'member_name',
                    name: 'member_name'
                },
                {
                    data: 'totaldepositinv',
                    name: 'totaldepositinv'
                },
                {
                    data: 'last_recovery_date',
                    name: 'last_recovery_date'
                },
                {
                    data: 'associate_code',
                    name: 'associate_code'
                },
                {
                    data: 'associate_name',
                    name: 'associate_name'
                },
                {
                    data: 'amount',
                    name: 'amount'
                },
                {
                    data: 'transfer_date',
                    name: 'transfer_date'
                },
                {
                    data: 'loan_amount',
                    name: 'loan_amount'
                },
                {
                    data: 'file_charge',
                    name: 'file_charge'
                },
                {
                    data: 'ecs_ref_no',
                    name: 'ecs_ref_no'
                },
                {
                    data: 'ecs_charge',
                    name: 'ecs_charge'
                },
                {
                    data: 'igst_ecs_charge',
                    name: 'igst_ecs_charge'
                },
                {
                    data: 'cgst_ecs_charge',
                    name: 'cgst_ecs_charge'
                },
                {
                    data: 'sgst_ecs_charge',
                    name: 'sgst_ecs_charge'
                },
                {
                    data: 'igst_file_charge',
                    name: 'igst_file_charge'
                },
                {
                    data: 'cgst_file_charge',
                    name: 'cgst_file_charge'
                },
                {
                    data: 'sgst_file_charge',
                    name: 'sgst_file_charge'
                },
                {
                    data: 'insurance_charge',
                    name: 'insurance_charge'
                },
                {
                    data: 'igst_insurance_charge',
                    name: 'igst_insurance_charge'
                },
                {
                    data: 'cgst_insurance_charge',
                    name: 'cgst_insurance_charge'
                },
                {
                    data: 'sgst_insurance_charge',
                    name: 'sgst_insurance_charge'
                },
                {
                    data: 'bank_name',
                    name: 'bank_name'
                },
                {
                    data: 'bank_account_number',
                    name: 'bank_account_number'
                },
                {
                    data: 'ifsc_code',
                    name: 'ifsc_code'
                },
                {
                    data: 'reason',
                    name: 'reason'
                },
                {
                    data: 'status',
                    name: 'status'
                },
                {
                    data: 'approve_date',
                    name: 'approve_date'
                },
                {
                    data: 'created_at',
                    name: 'created_at'
                },
				 {
                    data: 'running_loan_account_number',
                    name: 'running_loan_account_number'
                },
				 {
                    data: 'running_loan_closing_amount',
                    name: 'running_loan_closing_amount'
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                },
            ],"ordering": false,
        });

        $('#loan-filter').validate({
            rules: {
                company_idd:{
                    required: true,
                },
                loan_type: {
                    required: true,
                },
                application_number: {
                    number: true,
                    minlength: 8,
                    maxlength: 16
                },
                member_id: {
                    number: true
                },
                associate_code: {
                    number: true
                },
                group_loan_common_id: {
                    number: true
                },
            },
            messages: {
                loan_type: {
                    required: 'Please select loan type',
                },
            },
            submitHandler: function() {
                $('#is_search').val("yes");
                $(".table-section").removeClass("d-none");
                // var formdata = $('.loan_filter').serialize();
                // showlisting();
                loanRequestTable.draw();
            }

        });

        // function showlisting() {
        //     var company_id = $('#company_id').val();
        //     var date_from = $('input[name="date_from"]').val();
        //     var date_to = $('input[name="date_to"]').val();
        //     var branch_id = $('#branch_id').val();
        //     var application_number = $('input[name="application_number"]').val();
        //     var member_name = $('input[name="member_name"]').val();
        //     var member_id = $('input[name="member_id"]').val();
        //     var associate_code = $('input[name="associate_code"]').val();
        //     var loan_type = $('#loan_type').val();
        //     var loan_plan = $('#loan_plan').val();
        //     var status = $('#status').val();
        //     var is_search = $('input[name="is_search"]').val();
        // }
        $(loanRequestTable.table().container()).removeClass('form-inline');
        $('#reset_form').click(function() {
            $(".loan_filter").trigger('reset');
            $('#is_search').val("no");
            // $(".table-section").removeClass("show-table");
            $(".table-section").addClass("d-none");
        })





    });
</script>
@endsection
