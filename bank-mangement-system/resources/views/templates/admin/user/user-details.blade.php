@extends('templates.admin.master')

@section('content')
<div class="content"> 
    <div class="row">  
        <div class="col-md-12" id="print_recipt">
            <div class="card">
                <div class="card-header header-elements-inline">
                    <h4 class="card-title font-weight-semibold  col-lg-12 text-center">User Detail</h4>
                </div>
                <div class="card-body"> 
                  <div class="  row">
                    <label class=" col-lg-4  ">User Name : </label>
                    <div class="col-lg-8   ">
                      {{ $user->username }}
                    </div>
                  </div>
                  <div class="  row">
                    <label class=" col-lg-4  ">Employee Name : </label>
                    <div class="col-lg-8   ">
                      {{ $employee->employee_name }}
                    </div>
                  </div> 
                  <div class="  row">
                    <label class=" col-lg-4  ">Employee Code : </label>
                    <div class="col-lg-8   ">
                      {{ $employee->employee_code }}
                    </div>
                  </div>
                  <div class="  row">
                    <label class=" col-lg-4  ">User Id : </label>
                    <div class="col-lg-4   ">
                      {{ $employee->user_id }}
                    </div>
                  </div>
                  <div class="  row">
                    <label class=" col-lg-4  ">Status : </label>
                    <div class="col-lg-4   ">
                      @if($user->status == 0)
                        Active
                      @else
                        Deactive
                      @endif
                    </div>
                  </div>
                </div>
            </div>
        </div>
    </div>
</div>
@include('templates.admin.member.partials.script')
@stop