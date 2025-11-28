@extends('templates.admin.master')

@section('content')
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
                                
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Category </label>
                                        <div class="col-lg-12 error-msg">
                                            <select class="form-control" id="category" name="category">
                                                <option value="">Select Category</option>
                                                <option value="all"   >All</option> 
                                                <option value="1"  >On-rolled</option>  
                                                <option value="2" >Contract</option> 
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Designation </label>
                                        <div class="col-lg-12 error-msg">
                                            <select class="form-control" id="designation" name="designation">
                                                <option value="">Select Designation</option> 
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Employee Name</label>
                                        <div class="col-lg-12 error-msg">
                                             <div class="input-group"> 

                                                 <input type="text" name="employee_name" id="employee_name" class="form-control"> 
                                               </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Employee Code</label>
                                        <div class="col-lg-12 error-msg">
                                             <div class="input-group">
                                                 <input type="text" name="employee_code" id="employee_code" class="form-control"> 
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
                                                    <option value="1" selected>Fully Transferred</option> 
                                                    <option value="2">Partial Transfer</option> 
                                                    
                                                </select>
                                               </div>
                                        </div>
                                    </div>
                                </div>
                               

                                <div class="col-md-12">
                                    <div class="form-group text-right"> 
                                        <div class="col-lg-12 page">

                                            <button type="button" class=" btn bg-dark legitRipple"    onclick="searchForm()">Submit</button>
                                            <input type="hidden" name="is_search" id="is_search" value="yes">
                                            <input type="hidden" name="export" id="export" >
                                            <input type="hidden" name="leaser_id" id="leaser_id" value="{{$leaser_id}}">
                                            <button type="button" class="btn btn-gray legitRipple" id="reset_form" onClick="resetForm()" >Reset </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
        </div>
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">Employee's Transferred Salary List
                    - {{$leaserData->month_name}} {{$leaserData->year}} - {{$leaserData->company->name}}</h6>
                        <div class="">
                            <button type="button" class="btn bg-dark legitRipple export ml-2" data-extension="0" style="float: right;">Export xslx</button>
                           <!-- <button type="button" class="btn bg-dark legitRipple export" data-extension="1">Export PDF</button>-->
                        </div>
                        <input type="hidden" class="form-control created_at " name="created_at" id="created_at"  >
                        <input type="hidden" class="form-control create_application_date " name="create_application_date" id="create_application_date"  >
                    </div>
                    <div class="">
                        <table id="salary_list" class="table datatable-show-all">
                            <thead>
                                <tr>
                                    <tr>                                    
                                        <th >S.No</th>
                                        <th >Category</th> 
                                        <th >Designation</th>
                                        <th >BR Name</th>                                         
                                        <th >Employee Name </th>
                                        <th >Employee Code </th>
                                        
                                        <th >Gross Salary</th>
                                        <th >Leave</th>
                                        <th >Total Salary</th>
                                        <th >Deduction</th>
                                        <th >Incentive / Bonus </th>

                                        <th >Payable Amount </th>
                                        <th >ESI Amount </th>
                                        <th >PF Amount </th>
                                        <th >TDS Amount </th> 
                                        
                                        <th >Final Payable Salary </th>
                                        <th >Advance Salary</th>
                                        <th >Settle Salary</th>
                                        <th >Transferred Salary </th> 
                                        <th >Transferred In </th> 
                                        <th >Employee SSB</th>  
                                        <th >Employee Bank Name</th>
                                        <th >Employee Bank A/c </th>
                                        <th >Employee Bank IFSC </th>
                                        <th >Transferred Date </th> 
                                        <th > Bank Name </th> 
                                        <th > Bank A/C </th>
                                        <th >Payment Mode</th> 
                                        <th >Cheque No.</th> 
                                        <th >Online UTR/tractions No.</th>  
                                        <th >RTGS/NEFT Charge</th> 
                                        <th >Is Transferred</th> 
                                        <th >Action</th> 
                                         
                                        </tr>   
                                </tr>
                            </thead>                    
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@include('templates.admin.hr_management.salary.script_salary_list')
@stop