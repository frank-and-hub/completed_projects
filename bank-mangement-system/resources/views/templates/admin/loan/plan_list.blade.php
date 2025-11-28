@extends('templates.admin.master')

@section('content')
<div class="content">
        <div class="row">
            <div class="col-md-12">

                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">Search Filter</h6>
                        <a class="add-new" title=" Create Plan " href="{{route('admin.loan.planCreate')}}">
                                        <i style="font-size:24px" class="fa">&#xf067;</i>
                                        <input type="hidden" name="created_at" id="created_at"  class="created_at">
                                </a>
                    </div> 
                    <div class="card-body">
                        {{ Form::open(['url'=>'','method'=>'POST','name'=>'searchForm','id'=>'searchForm','class'=>'searchForm']) }}
                            <!-- <div class="card mb-0"> -->
                                        <!-- <div class="card-body"> -->
                                            <!-- <div class="form-group row"> -->
                                                <!-- <div class="col-lg-6"> -->
                                                        <div class="form-group row">
                                                            <div class=" col-lg-12 error-msg getcompany_id">
                                                                  @include('templates.GlobalTempletes.role_type',['dropDown'=>$company,'filedTitle'=>'Company','name'=>'searchCompanyId','apply_col_md'=>true])
                                                            </div>
                                                        </div>
                                                <!-- </div> -->
                                            <!-- </div> -->
                                            {{Form::button('<i class="fa fa-submit"></i> SUBMIT', ['class' => 'btn btn-block btn-primary mb-3 float-right','type'=>'submit','id'=>'search','style'=>'width:auto;']) }}
                                        <!-- </div> -->
                                    <!-- </div> -->
                        {{ Form::close() }}
                    </div>
                </div>

                <div class="card bg-white shadow data_div d-none">

					
					<div class="card-header bg-transparent header-elements-inline">
						<h3 class="mb-0 text-dark">Loan Plan - Listing </h3>
						<div class="right-btn-section">
                           
                            <div class="right-btn-section">
                                
                                
                            </div>	
                          
                        </div>		
					</div>
					
                    <div class="table-responsive">
                        <table id="loan_plan_table" class="table datatable-show-all">
                            <thead>
                                <tr>
                                    <th>S/N</th>
                                    <th>Loan Type</th>
                                    <th>Company Name</th>
                                    <th>Name</th>                                                                  
                                    <th>Code</th>
                                    <th>Category</th>                                                                                                      
                                    <th>Minimum Amt.</th>
                                    <th>Maximum Amt.</th>
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