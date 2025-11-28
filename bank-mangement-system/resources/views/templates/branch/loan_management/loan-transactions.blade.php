@extends('layouts/branch.dashboard')

@section('content')
<style type="text/css">
  .datepicker.dropdown-menu {
    z-index: 9999999 !important;
}
</style>
<div class="container-fluid mt--6">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-lg-12">
                <div class="card bg-white">
                <div class="card-body">
                    <div class="">
                        <h3 class="">Loan Transaction Listing</h3>
                    </div>
                </div>
                </div>
            </div>
        </div>
        
     <div class="row">    
       
         <div class="col-md-12">
            <div class="card">
                <div class="card-header header-elements-inline">
                    <h3 class="card-title font-weight-semibold">Search Filter</h3>
                </div>
                <div class="card-body">
                <form action="#" method="post" enctype="multipart/form-data" id="transaction-loan-filter" name="loan-filter">
                    @csrf
                        <div class="row">
                            @include('templates.GlobalTempletes.role_type',[
                                'dropDown'=> $branchCompany[Auth::user()->branches->id],
                                'name'=>'transaction_loan_company_id',
                                'apply_col_md'=>false,
                                'filedTitle' => 'Company'
                            ])
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Loan Type <span class="required">*</span></label>
                                    <div class="col-lg-12 error-msg">
                                        <!-- <div class="input-group"> -->
                                            <select class="form-control" id="loan_type" name="loan_type">
                                                <option value=""  >----Select Loan Type----</option> 
                                                <option value="L">Loan</option>
                                                <option value="G">Group Loan</option>
                                            </select>
                                           <!-- </div> -->
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Loan Plan</label>
                                    <div class="col-lg-12 error-msg">
                                        <div class="input-group">
                                            <select class="form-control" id="plan" name="plan">
                                                <option value=""  >----Select Loan Plan----</option> 
                                            </select>
                                           </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Date From</label>
                                    <div class="col-lg-12 error-msg">
                                         <div class="input-group">
                                             <input type="text" class="form-control from_date" name="date_from" autocomplete="off"> 
                                           </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Date To</label>
                                    <div class="col-lg-12 error-msg">
                                         <div class="input-group">
                                             <input type="text" class="form-control to_date" name="date_to" autocomplete="off"> 
                                           </div>
                                    </div>
                                </div>
                            </div>
                          <!-- @if(Auth::user()->branch_id<1)
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Select Branch </label>
                                    <div class="col-lg-12 error-msg">
                                        <div class="input-group">
                                            <select class="form-control" id="branch_id" name="branch_id">
                                                <option value=""  >----Select----</option> 
                                                @foreach( App\Models\Branch::pluck('name', 'id') as $key => $val )
                                                    <option value="{{ $key }}"  >{{ $val }}</option> 
                                                @endforeach
                                            </select>
                                           </div>
                                    </div>
                                </div>
                            </div>
                           @else -->
                              <input type="hidden" name="branch_id" id="branch_id" value="{{Auth::user()->branch_id}}">                         
                            <!-- @endif -->
                              
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Account Number </label>
                                    <div class="col-lg-12 error-msg">
                                        <input type="text" class="form-control  " name="application_number" id="application_number"  >
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Associate code  </label>
                                    <div class="col-lg-12 error-msg">
                                        <input type="text" name="associate_code" id="associate_code" class="form-control"  > 
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Payment Mode</label>
                                    <div class="col-lg-12 error-msg">
                                        <div class="input-group">
                                            <select class="form-control" id="payment_mode" name="payment_mode">
                                                <option value=""  >----Select----</option> 
                                                 <option value="0"  >Cash</option> 
                                                 <option value="1"  >Bank </option> 
                                                 <option value="4"  >SSb</option> 
                                                 
                                            </select>
                                           </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">&nbsp;</label>
                                    <div class="col-lg-12 error-msg">
                                       &nbsp;
                                    </div>
                                </div>
                            </div>
                            
                            
                            
                            <div class="col-md-12">
                                <div class="form-group row"> 
                                    <div class="col-lg-12 text-right" >
                                        <input type="hidden" name="is_search" id="is_search" value="no">
                                        <input type="hidden" name="loan_transaction_export" id="loan_transaction_export" value="">
                                        <button type="button" class=" btn btn-primary legitRipple" onClick="loanTransactionSearchForm()" >Submit</button>
                                        <button type="button" class="btn btn-gray legitRipple" id="reset_form" onClick="loanTransactionResetForm()" >Reset </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
            <div class="col-lg-12 shadow d-none">                

                <div class="card bg-white">
                    <div class="card-header bg-transparent">
                        <div class="row">
                            <div class="col-md-8">
                            <h3 class="mb-0 text-dark">Loans Transaction</h3>
                        </div>
                            <div class="col-md-4 text-right">
                            <button type="button" class="btn btn-primary legitRipple export-loan-transaction ml-2" data-extension="0" style="float: right;">Export Excel</button>
                            <!-- <button type="button" class="btn bg-dark legitRipple export-loan-transaction" data-extension="1">Export PDF</button> -->
                        </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                    <table id="loan_transaction_table" class="table datatable-show-all">
                        <thead>
                            <tr>
                                <th>S/N</th>
                                <th>Created Date</th>                           
                                <th>Company</th>                           
                                <th>Member Id</th>
                                <th>Customer Id</th>
                                <th>Account No.</th>
                                <th>Member(Account Holder Name)</th>
                                <th>Loan Type </th>
                                <th>Tenure</th>
                                <th>Emi Amount </th>
                                <th>Transaction Type</th>
                                <th>Associate Code</th>
                                <th>Associate Name</th>
                                <th>Payment Mode</th>
                                <!--<th>Action</th>-->
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
<script src="{{url('/')}}/asset/js/sweetalert.min.js"></script>
<script>
    $('.export-loan-transaction').on('click', function(e) {
            e.preventDefault();
            var extension = $(this).attr('data-extension');
            $('#loan_transaction_export').val(extension);
            if (extension == 0) {
                var formData = jQuery('#loan-filter').serializeObject();
                var chunkAndLimit = 50;
                $(".spiners").css("display", "block");
                $(".loaders").text("0%");
                doChunkedExports(0, chunkAndLimit, formData, chunkAndLimit, 1);
                $("#cover").fadeIn(100);
            } else {
                var start_date = $('#start_date').val();
                $('#from_date').val(start_date);
                $('#start_date').val(start_date);
                $('#export').val(extension);
                $('form#filter').attr('action', "{!! route('branch.loantransaction.export') !!}");
                $('form#filter').submit();
            }
        });


        // function to trigger the ajax bit
        function doChunkedExports(start, limit, formData, chunkSize, page) {
            formData['start'] = start;
            formData['limit'] = limit;
            formData['page'] = page;
            jQuery.ajax({
                type: "post",
                dataType: "json",
                url: "{!! route('branch.loantransaction.export') !!}",
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.result == 'next') {
                        start = start + chunkSize;
                        page = page + 1;
                        doChunkedExports(start, limit, formData, chunkSize, page);
                        $(".loaders").text(response.percentage + "%");
                    } else {
                        var csv = response.fileName;
                        console.log('DOWNLOAD');
                        $(".spiners").css("display", "none");
                        $("#cover").fadeOut(100);
                        window.open(csv, '_blank');
                    }
                }
            });
        }
        jQuery.fn.serializeObject = function() {
            var o = {};
            var a = this.serializeArray();
            jQuery.each(a, function() {
                if (o[this.name] !== undefined) {
                    if (!o[this.name].push) {
                        o[this.name] = [o[this.name]];
                    }
                    o[this.name].push(this.value || '');
                } else {
                    o[this.name] = this.value || '';
                }
            });
            return o;
        };
</script>
@include('templates.branch.loan_management.partials.script')
@stop