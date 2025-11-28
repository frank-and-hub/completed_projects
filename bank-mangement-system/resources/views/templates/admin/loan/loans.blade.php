@extends('templates.admin.master')

@section('content')
<div class="content">
        <div class="row">

            <div class="col-md-12">
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h3 class="card-title font-weight-semibold">Search Filter</h3>
                    </div>
                    <div class="card-body">
                        <form action="#" method="post" enctype="multipart/form-data" id="filter" name="filter">
                        @csrf
                            <div class="row">

                                @php
                                    $dropDown = $company;
                                    $filedTitle = 'Company';
                                    $name = 'company_id';
                                @endphp

                                @include('templates.GlobalTempletes.new_role_type',['dropDown'=>$dropDown,'filedTitle'=>$filedTitle,'name'=>$name,'value'=>'','multiselect'=>'false','design_type'=>4,'branchShow'=>true,'branchName'=>'branch_id','apply_col_md'=>true,'multiselect'=>false,'placeHolder1'=>'Please Select Company','placeHolder2'=>'Please Select Branch'])
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Loan Type <span class="required">*</span></label>
                                        <div class="col-lg-12 error-msg">
                                            <!-- <div class="input-group"> -->
                                                <select class="form-control loan_type" id="loan_type" name="loan_type">
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
                                            <!-- <div class="input-group"> -->
                                                <select class="form-control" id="plan" name="plan">
                                                    <option value=""  >----Select Loan Plan----</option> 
                                                </select>
                                               <!-- </div> -->
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-12">
                                    <div class="form-group row"> 
                                        <div class="col-lg-12 text-right" >
                                            <input type="hidden" name="is_search" id="is_search" value="no">
                                          <input type="hidden" name="loan_recovery_export" id="loan_recovery_export" value="">
                                            <button type="button" class=" btn btn-primary legitRipple" onClick="searchForm()" >Submit</button>
                                            <button type="button" class="btn btn-gray legitRipple" id="reset_form" onClick="resetForm()" >Reset </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-12 d-none">
                <div class="card bg-white shadow">

					
					<div class="card-header bg-transparent header-elements-inline">
						<h3 class="mb-0 text-dark">Loan Tenure - Listing</h3>
						<div class="right-btn-section">
                            @if(check_my_permission( Auth::user()->id,"303") == "1")
                            <div class="right-btn-section">
                                <a class="add-new" title=" Create loan" href="{{route('admin.loan.create')}}">
                                        <i style="font-size:24px" class="fa">&#xf067;</i>
                                        <input type="hidden" name="created_at" id="created_at"  class="created_at">
                                </a>
                                
                            </div>	
                            @endif	
                        </div>		
					</div>
					
                    <div class="table-responsive">
                        
                        <table id="loan_table" class="table datatable-show-all">
                            <thead>
                                <tr>
                                    <th>S/N</th>
                                    <th>Loan Type</th>
                                    <th>Name</th> 
                                    <th>Tenure</th>
                                    <th>EMI Option</th>
                                    <th>ROI</th>  
                                    <th>Effective From</th>
                                    <th>Effective To</th>  
                                    <th>Created By</th>
                                    <th>Status</th>
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
@include('templates.admin.loan.partials.settingscript') 
@stop