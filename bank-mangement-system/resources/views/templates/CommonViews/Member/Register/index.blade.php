@php
    $admin = Auth::user()->role_id != 3 ? true : false;
    $pathLayout = $admin == true ? 'templates.admin.master' : 'layouts.branch.dashboard';
@endphp
@extends($pathLayout)

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
                        {{Form::open(['url'=>'#','method'=>'POST','enctype'=>'multipart/form-data','id'=>'filter','name'=>'filter'])}}
                            <div class="row">
                                {{--
                                @php
                                    $dropDown = $company;
                                    $filedTitle = 'Company';
                                    $name = 'company_id';
                                @endphp
                                   
                                @include('templates.GlobalTempletes.new_role_type',['dropDown'=>$dropDown,'filedTitle'=>$filedTitle,'name'=>$name,'value'=>'','multiselect'=>'false','design_type'=>4,'branchShow'=>true,'branchName'=>'branch_id','apply_col_md'=>false,'multiselect'=>false,'placeHolder1'=>'Please Select Company','placeHolder2'=>'Please Select Branch']) --}}
                                
                                @include('templates.GlobalTempletes.both_company_filter',['all'=>true])
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">From Date </label>
                                        <div class="col-lg-12 error-msg">
                                             <div class="input-group">
                                                {{Form::text('start_date','',['id'=>'start_date','class'=>'form-control','autocomplete'=>'off'])}} 
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">To Date </label>
                                        <div class="col-lg-12 error-msg">
                                            <div class="input-group">
                                                {{Form::text('end_date','',['id'=>'end_date','class'=>'form-control','autocomplete'=>'off'])}}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Member Name </label>
                                        <div class="col-lg-12 error-msg">
                                            {{Form::text('name','',['id'=>'name','class'=>'form-control','autocomplete'=>'off'])}}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Member ID </label>
                                        <div class="col-lg-12 error-msg">
                                            {{Form::text('member_id','',['id'=>'member_id','class'=>'form-control','autocomplete'=>'off'])}}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Associate code  </label>
                                        <div class="col-lg-12 error-msg">
                                            {{Form::text('associate_code','',['id'=>'associate_code','class'=>'form-control','autocomplete'=>'off'])}} 
                                        </div>
                                    </div>
                                </div> 
                               
                                                    
                               
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Customer ID </label>
                                        <div class="col-lg-12 error-msg">
                                            {{Form::text('customer_id','',['id'=>'customer_id','class'=>'form-control','autocomplete'=>'off'])}}
                                        </div>
                                    </div>
                                </div> 

                               <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Status</label>
                                        <div class="col-lg-12 error-msg">
                                            {{Form::select('status',[''=>'Select Status','0'=>'Active','1'=>'Blocked'],'',['id'=>'status','class'=>'form-control'])}}
                                        </div>
                                    </div>
                                </div>
                               
                                
                                
                                <div class="col-md-12">
                                    <div class="form-group row"> 
                                        <div class="col-lg-12 text-right" >
                                            {{Form::hidden('is_search','no',['id'=>'is_search','class'=>'form-control'])}}
                                            {{Form::hidden('member_export','',['id'=>'member_export','class'=>'form-control'])}}
                                            <button type="button" class=" btn bg-dark legitRipple" onClick="searchForm()" >Submit</button>
                                            <button type="button" class="btn btn-gray legitRipple" id="reset_form" onClick="resetForm()" >Reset </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        {{Form::close()}}
                    </div>
                </div>
            </div>
            <div class="col-md-12 table-section hideTableData">
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">Members List</h6>
                        <div class="">
                            <button type="button" class="btn bg-dark legitRipple export ml-2" data-extension="0" style="float: right;">Export Excel</button>
                            
                        </div>
                    </div>
                    <div class="">
                        <table id="member_listing" class="table datatable-show-all">
                            <thead>
                                <tr>
                                    <th>S/N</th>
                                    <th>Company</th>
                                    <th>Br Name</th> 
                                    <th>Member Name</th>
                                    <th>Member ID</th>
                                    <th>Customer ID</th>
                                    <th>Join Date</th>
                                    <th>DOB</th>                                   
                                    <th>Gender</th>
                                    <th>Mobile</th>
                                    <th>State</th>
                                    <th>District</th> 
                                    <th>City</th>
                                    <th>Address</th>
                                    <th>Village Name</th> 
                                    <th>Pin Code</th>
                                    <th>ID Proof</th>                               
                                    <th>Address Proof</th>
                                    <th>Nominee Name</th>
                                    <th>Age</th>
                                    <th>Relation</th>
                                    <th>Nominee Gender</th>
                                    <th>Associate Name</th>
                                    <th>Associate Code</th>  
                                    <th>Status</th> 
                                    <th>SSB A/C</th>
                                    <th>Image Uploaded</th>                                  
                                   <th class="text-center">Action</th>    
                                </tr>
                            </thead>                    
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@include('templates.admin.member.partials.listing_script')
@stop