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
                            @include('templates.GlobalTempletes.both_company_filter')
 
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Month</label>
                                        <div class="col-lg-12 error-msg">
                                            <div class="input-group">
                                                <select class="form-control" id="rent_month" name="rent_month">
                                                    <option value=""  >----Select Month----</option> 
                                                 <!--   @for($i = 1; $i <= 12; $i++)
                                                    <option value="{{ $i }}"  >{{ $i }}</option> 
                                                    @endfor -->

                                                    {{ $last_month= 12}}
                                                        {{ $now_month = 1}}

                                                        @for ($i = $now_month; $i <= $last_month; $i++)
                                                        <?php
                                                        $monthNum  = $i;
                                                        $monthName = date('F', mktime(0, 0, 0, $monthNum, 10));
                                                        ?>
                                                            <option value="{{ $i }}">{{ $monthName }}</option>
                                                        @endfor

                                                </select>
                                               </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Year</label>
                                        <div class="col-lg-12 error-msg">
                                            <div class="input-group">
                                                <select class="form-control" id="rent_year" name="rent_year">
                                                    <option value=""  >----Select Year----</option> 
                                                  <!--  @for($i = 2000; $i <= 2050; $i++)
                                                    <option value="{{ $i }}"  >{{ $i }}</option> 
                                                    @endfor-->

                                                    {{ $last= date('Y')-100 }}
                                                        {{ $now = date('Y') }}

                                                        @for ($i = $now; $i >= 2019; $i--)
                                                            <option value="{{ $i }}">{{ $i }}</option>
                                                        @endfor

                                                </select>
                                               </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Status</label>
                                        <div class="col-lg-12 error-msg">
                                            <div class="input-group">
                                                <select class="form-control" id="status" name="status">
                                                    <option value=""  >----Select Status----</option> 
                                                  
                                                    <option value="0">Pending</option>
                                                    <option value="1">Transferred</option>
                                                    <option value="2">Partial Transfer</option>

                                                </select>
                                               </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group row"> 
                                        <div class="col-lg-12 text-right" >
                                            <input type="hidden" name="is_search" id="is_search" value="no">
                                            <input type="hidden" name="ledger_export" id="ledger_export" value="">
                                            <button type="button" class=" btn bg-dark legitRipple filter-report" onClick="searchForm()" >Submit</button>
                                            <button type="button" class="btn btn-gray legitRipple filter-report" id="reset_form" onClick="resetForm()" >Reset </button>
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
                        <h6 class="card-title font-weight-semibold">Rent Ledger </h6>
                        <div class="">
                        
                            <button type="button" class="btn bg-dark legitRipple export ml-2" data-extension="0" style="float: right;">Export xslx</button>
                            
                        </div>
                    </div>
                    <table class="table datatable-show-all" id="rent-ledger-table">
                        <thead>
                            <tr>
                                <th >S.No</th>
                                <th >Company Name</th>
                                <th >Month</th>
                                <th >Year</th>
                                <th >Total Amount</th>
                                <th >TDS Amount</th>
                                <th >Payable Amount</th>
                                <th >Transferred  Amount</th>
                                <th >Pending Amount</th> 
                                <th >NEFT Charge</th> 
                                <th >Status</th>
                                <th >Created</th>
                                <th >Action</th>

                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop

@section('script')
<script>
    $('#branch').parents('.col-md-4').addClass('d-none');
</script>
@include('templates.admin.rent-management.partials.ledger')
@endsection
