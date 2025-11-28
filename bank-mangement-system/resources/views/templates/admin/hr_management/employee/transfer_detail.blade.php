@extends('templates.admin.master')

@section('content')

<div class="content">   
       
<div class="row">
        

        <div class="col-lg-12">
          <div class="card bg-white" > 
            <div class="card-body">
              <h3 class="card-title mb-3">Employee Information</h3>
              <div class="row">
                <div class="col-lg-6">
                    <div class=" row">
                        <label class=" col-lg-5">Company Name : </label>
                        <div class="col-lg-7 ">
                       
                     {{$employee['transferEmployee']['company']->name}} 
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                  <div class=" row">
                      <label class=" col-lg-5">Branch Name: </label>
                      <div class="col-lg-7 ">
                        {{ $employee['transferBranchOld']->name}}
                      </div>
                    </div>
                </div>
                <div class="col-lg-6">
                  <div class=" row">
                      <label class=" col-lg-5">Designation : </label>
                      <div class="col-lg-7 "> 
                        {{ $employee['transferEmployee']['designation']->designation_name}}
                      </div>
                    </div>
                </div>
                <div class="col-lg-6">
                  <div class="row">
                      <label class=" col-lg-5">Gross Salary : </label>
                      <div class="col-lg-7 ">  
                        {{  number_format((float)$employee['transferEmployee']->salary, 2, '.', '')}} <img src='{{url('/')}}/asset/images/rs.png' width='7'>
                      </div>
                    </div>
                </div>
                <div class="col-lg-6">
                  <div class="row">
                      <label class=" col-lg-5">Employee Code : </label>
                      <div class="col-lg-7 "> 
                        {{$employee['transferEmployee']->employee_code}}
                      </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class=" row">
                        <label class=" col-lg-5">Category : </label>
                        <div class="col-lg-7 ">
                          
                          @if($employee['transferEmployee']->category==1)
                          On-rolled
                          @else
                          Contract
                          @endif 
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="row">
                    <label class=" col-lg-5">Employee Name : </label>
                    <div class="col-lg-7 ">
                      {{$employee['transferEmployee']->employee_name}}
                    </div>
                  </div>
                </div>
                <div class="col-lg-6">
                  <div class="row">
                      <label class=" col-lg-5">Status : </label>
                      <div class="col-lg-7 "> 
                        @if($employee['transferEmployee']->status==1)
                            Active 
                          @else
                            Inactive
                          @endif
                      </div>
                    </div>
                </div>         

              </div> 
            </div>
          </div> 

          <div class="card bg-white" > 
            <div class="card-body">
              <h3 class="card-title mb-3">Transferred Information</h3>
              <div class="row">
                <div class="col-lg-6">
                    <div class=" row">
                        <label class=" col-lg-5">Apply Date : </label>
                        <div class="col-lg-7 ">{{date("d/m/Y", strtotime($employee->apply_date))}} 
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class=" row">
                        <label class=" col-lg-5">Transferred Date : </label>
                        <div class="col-lg-7 ">{{date("d/m/Y", strtotime($employee->transfer_date))}} 
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                  <div class=" row">
                      <label class=" col-lg-5">Branch : </label>
                      <div class="col-lg-7 ">
                        {{ $employee['transferBranchOld']->name}}
                      </div>
                    </div>
                </div>
                <div class="col-lg-6">
                  <div class=" row">
                      <label class=" col-lg-5">Transferred Branch : </label>
                      <div class="col-lg-7 ">
                        {{ $employee['transferBranch']->name}}
                      </div>
                    </div>
                </div>
                <div class="col-lg-6">
                  <div class=" row">
                      <label class=" col-lg-5">Designation : </label>
                      <div class="col-lg-7 "> 
                        {{ getDesignationData('designation_name',$employee->old_designation_id)->designation_name}}
                      </div>
                    </div>
                </div>
                <div class="col-lg-6">
                  <div class=" row">
                      <label class=" col-lg-5">Transfer Designation : </label>
                      <div class="col-lg-7 "> 
                        {{ getDesignationData('designation_name',$employee->designation_id)->designation_name}}
                      </div>
                    </div>
                </div>
                <div class="col-lg-6">
                  <div class="row">
                      <label class=" col-lg-5">Gross Salary : </label>
                      <div class="col-lg-7 "> 
                       {{  number_format((float)$employee->old_salary, 2, '.', '')}} <img src='{{url('/')}}/asset/images/rs.png' width='7'>
                      </div>
                    </div>
                </div>
                <div class="col-lg-6">
                  <div class="row">
                      <label class=" col-lg-5">Transferred Gross Salary  : </label>
                      <div class="col-lg-7 "> 
                        {{  number_format((float)$employee->salary, 2, '.', '')}} <img src='{{url('/')}}/asset/images/rs.png' width='7'>
                      </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class=" row">
                        <label class=" col-lg-5">Category : </label>
                        <div class="col-lg-7 ">
                          
                          @if($employee->old_category==1)
                          On-rolled
                          @else
                          Contract
                          @endif 
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class=" row">
                        <label class=" col-lg-5">Transferred Category : </label>
                        <div class="col-lg-7 ">
                          
                          @if($employee->category==1)
                          On-rolled
                          @else
                          Contract
                          @endif 
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="row">
                    <label class=" col-lg-5">Recommendation Employee Designation : </label>
                    <div class="col-lg-7 ">
                      {{$employee->old_recom_designation}}
                    </div>
                  </div>
                </div>
                <div class="col-lg-6">
                    <div class="row">
                    <label class=" col-lg-5">Transferred Recommendation Employee Designation : </label>
                    <div class="col-lg-7 ">
                      {{$employee->recom_designation}}
                    </div>
                  </div>
                </div>
                <div class="col-lg-6">
                    <div class="row">
                    <label class=" col-lg-5">Recommendation Employee Name : </label>
                    <div class="col-lg-7 ">
                      {{$employee->old_recommendation_name}}
                    </div>
                  </div>
                </div>
                <div class="col-lg-6">
                    <div class="row">
                    <label class=" col-lg-5">Transferred Recommendation Employee Name : </label>
                    <div class="col-lg-7 ">
                      {{$employee->recommendation_name}}
                    </div>
                  </div>
                </div>
                <div class="col-lg-6">
                    <div class="row">
                    <label class=" col-lg-5">Transferred File: </label>
                    <div class="col-lg-7 ">
                      
                      <a href="{{url('/')}}/asset/employee/transfer/{{$employee->file}}"  target='_blank'  >{{$employee->file}} </a>
                    </div>
                  </div>
                </div>
                <div class="col-lg-6">
                    <div class="row">
                    <label class=" col-lg-5">Transferred Letter : </label>
                    <div class="col-lg-7 ">
                      
                      <a href="{{url('/')}}/admin/hr/employee/transfer_letter/{{$employee->employee_id}}?type={{$employee->id}}"  target='_blank'  >Transfer Latter</a>
                    </div>
                  </div>
                </div>

              </div> 
            </div>
          </div>
        </div>
        


      </div> 
</div>

@stop