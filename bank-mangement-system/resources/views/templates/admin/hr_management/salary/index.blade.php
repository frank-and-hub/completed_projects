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
                        <h6 class="card-title font-weight-semibold">Filter</h6>
                    </div>
                    <div class="card-body">
                       <form action="#" method="post" enctype="multipart/form-data" id="filter" name="filter">
                        @csrf
                        <input type="hidden" name="created_at" class="created_at">
                            <div class="row">
                            @include('templates.GlobalTempletes.role_type',[
                                    'dropDown'=> $company,
                                    'name'=>'company_id',
                                    'apply_col_md'=>true,
                                    'filedTitle' => 'Company Name'
                                    ]) 
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Year</label>
                                        <div class="col-lg-12 error-msg">
                                             <div class="input-group">
                                                 <select name="year" id="year" class="form-control" >
                                                      <option value="">Select  Year</option>
                                                      {{ $last= 2020 }}
                                                        {{ $now = date('Y') }}

                                                        @for ($i = $now; $i >= $last; $i--)
                                                            <option value="{{ $i }}">{{ $i }}</option>
                                                        @endfor
                                                  </select>
                                               </div>
                                        </div>
                                    </div>
                                </div>    
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Month</label>
                                        <div class="col-lg-12 error-msg">
                                             <div class="input-group"> 

                                                 <select name="month" id="month" class="form-control" >
                                                      <option value="">Select  Month</option>
                                                       
                                                  </select>
                                               </div>
                                        </div>
                                    </div>
                                </div>
                               
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Status <sup>*</sup></label>
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
                                    <div class="form-group text-right"> 
                                        <div class="col-lg-12 page">

                                            <button type="button" class=" btn bg-dark legitRipple" onclick="searchForm()">Submit</button>
                                            <input type="hidden" name="is_search" id="is_search" value="no"> 
                                            <input type="hidden" name="export" id="export" >
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
                        <h6 class="card-title font-weight-semibold">Salary Ledger List</h6>
                        <div class="">
                            <button type="button" class="btn bg-dark legitRipple export ml-2" data-extension="0" style="float: right;">Export xslx</button>
                        <!--    <button type="button" class="btn bg-dark legitRipple export" data-extension="1">Export PDF</button>-->
                        </div>
                        <input type="hidden" class="form-control created_at " name="created_at" id="created_at"  >
                        <input type="hidden" class="form-control create_application_date " name="create_application_date" id="create_application_date"  >
                    </div>
                    <div class="">
                        <table id="salary_leaser" class="table datatable-show-all">
                            <thead>
                                <tr>
                                    <th >S.No</th>
                                    <th> Company Name</th>
                                    <th >Month</th>
                                    <th >Year</th>
                                    <th >Total Amount</th>
                                    <th >Transfrred Amount</th>
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
    </div>
@include('templates.admin.hr_management.salary.script_list')
@stop