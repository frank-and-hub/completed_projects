@extends('layouts/branch.dashboard')

@section('content')

<?php
$oldbranch='';
if($transfer['transferBranchOld']->address)
{
  $oldbranch.=$transfer['transferBranchOld']->address.', '.getCityName($transfer['transferBranchOld']->city_id).', '.getStateName($transfer['transferBranchOld']->state_id).', '.$transfer['transferBranchOld']->pin_code.'/';
}
if($transfer['transferBranchOld']->name)
{
  $oldbranch.=$transfer['transferBranchOld']->name.'/';
}
if($transfer['transferBranchOld']->sector)
{
  $oldbranch.=$transfer['transferBranchOld']->sector.'/';
}
if($transfer['transferBranchOld']->regan)
{
  $oldbranch.=$transfer['transferBranchOld']->regan.'/';
}
if($transfer['transferBranchOld']->zone)
{
  $oldbranch.=$transfer['transferBranchOld']->zone.'/';
}
if($transfer['transferBranchOld']->name)
{
  $oldbranch.=$transfer['transferBranchOld']->name.'';
}

$branch='';
if($transfer['transferBranch']->address)
{
  $branch.=$transfer['transferBranch']->address.', '.getCityName($transfer['transferBranch']->city_id).', '.getStateName($transfer['transferBranch']->state_id).', '.$transfer['transferBranch']->pin_code.'/';
}
if($transfer['transferBranch']->name)
{
  $branch.=$transfer['transferBranch']->name.'/';
}
if($transfer['transferBranch']->sector)
{
  $branch.=$transfer['transferBranch']->sector.'/';
}
if($transfer['transferBranch']->regan)
{
  $branch.=$transfer['transferBranch']->regan.'/';
}
if($transfer['transferBranch']->zone)
{
  $branch.=$transfer['transferBranch']->zone.'/';
}
if($transfer['transferBranch']->name)
{
  $branch.=$transfer['transferBranch']->name.'';
}
?>
<div class="loader" style="display: none;"></div>
<div class="container-fluid mt--6">
  <div class="content-wrapper">

      <div class="row"> 
        <div class="col-lg-12"> 
            
          <div class="card bg-white">
          <div class="card-body page-title"> 
                        <h3 class="">Employee Transfer Letter</h3>
            <a href="{{ redirect()->back()->getTargetUrl() }}" style="float:right" class="btn btn-secondary">Back</a>
          </div>
        </div>
        </div>
        <div class="col-lg-12" id="print_recipt"> 
            
          <div class="card bg-white" >
            <div class="card-body"> 
              <div class="  row"> 
                    <div class="col-lg-12   ">
                      <table style="width: 100%">
                        <tr>
                          <td style="width: 70%"><strong style="text-transform: capitalize;">{{$employee->employee_name}} </strong></td>
                          <td style="width: 30%" ><strong>Date -  {{date("d/m/Y ", strtotime($transfer->apply_date))}} </strong></td>
                        </tr>
                      </table>                     
                      <span class="text-right"> </span>
                    </div>
                    <div class="col-lg-12   ">{{$employee->employee_code}}
                    </div>
                    <div class="col-lg-12   ">
                      {{ getDesignationData('designation_name',$employee->designation_id)->designation_name}}
                    </div>
                    <div class="col-lg-12   ">
                       
                    </div>
                    <div class="col-lg-12   ">
                       <strong style="text-transform: capitalize;">{{$employee->company->name}} </strong>
                    </div>
                    <div class="col-lg-12   ">
                      <p></p>
                       <strong>Subject : </strong>Transfer Order.
                    </div>
                    <div class="col-lg-12   ">
                      <p></p>
                       Dear Mr. /Ms.,
                       <p></p>
                    </div>
                    <div class="col-lg-12   ">
                      <p>As per the Management's directives, your services are hereby transferred in the same capacity to <strong>({{$oldbranch}}  )</strong> w.e.f (<strong>{{date("d/m/Y ", strtotime($transfer->transfer_date))}}</strong>).</p>
                      <p>Your place of posting will be <strong>({{$branch}}  )</strong>. You are hereby instructed to report on duty to the above mentioned place of posting within 7 days of receiving of letter along with your relieving order, No dues certificate and balance leave status report. Other rules and regulations of the company remain effective and unchanged.</p>



                    </div>
                    <div class="col-lg-12"> Thank You</div>
                    <div class="col-lg-12"><strong>Sincerely,</strong></div>
                    <div class="col-lg-12" style="text-transform: capitalize;"><strong>{{$transfer->recommendation_name}}</strong></div>
                    <div class="col-lg-12" style="text-transform: capitalize;"><strong>{{$transfer->recom_designation}}</strong></div>
                    </div>
                  </div> 
            </div>
        </div>
        <div class="col-lg-12">
          <div class="card bg-white" >            
            <div class="card-body">
              <div class="text-center">
                  <button type="submit" class="btn btn-primary" onclick="printDiv('print_recipt');">Print<i class="icon-paperplane ml-2"></i></button>
               
              <a href="{{ redirect()->back()->getTargetUrl() }}" class="btn btn-secondary">Back</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>




@stop


@section('script')

@include('templates.branch.hr_management.employee.script_letter')
@stop