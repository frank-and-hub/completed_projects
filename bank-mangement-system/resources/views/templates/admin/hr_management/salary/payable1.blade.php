@extends('templates.admin.master')

@section('content')
<?php 
$re_month1='';
$re_year1=''; 
if(old('rent_month') )
{
    $re_month1=old('rent_month') ;
}
if(old('rent_year'))
{
    $re_year1=old('rent_year') ;
} 

if(isset($re_month))
{
    $re_month1=$re_month;
}
if(isset($re_year))
{
    $re_year1=$re_year;
} 
?>
<div class="content">
    <div class="row">  
        @if($errors->any())
            <div class="col-md-12">
                  <div class="alert alert-danger">
                      <ul>
                          @foreach ($errors->all() as $error)
                              <li>{{ $error }}</li>
                          @endforeach
                      </ul>
                  </div>
            </div>
        @endif

        <div class="col-md-12" id='hide_div'>
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">Employee Salary </h6>
                    </div>
                    <form action="{!! route('admin.hr.salary_generate') !!}" method="post" enctype="multipart/form-data"  name="salary_generate" id="salary_generate" >
                        @csrf 
                        <input type="hidden" name="created_at" class="created_at">
                    <div class="card-body">
                        <div class="row">
                           
                            <div class="col-md-12">
                                <div class="table-responsive py-4">
                                <table  class="table table-flush">
                                    <thead>
                                        <tr>                                    
                                        <th style="border: 1px solid #ddd;">S.No</th>
                                        <th style="border: 1px solid #ddd;">Category</th>  
                                        <th style="border: 1px solid #ddd;">BR Name</th> 
                                        <th style="border: 1px solid #ddd;">BR Code</th>
                                        <th style="border: 1px solid #ddd;">SO Name</th>
                                        <th style="border: 1px solid #ddd;">RO Name</th>
                                        <th style="border: 1px solid #ddd;">ZO Name</th> 
                                        <th style="border: 1px solid #ddd;">Employee Name </th>
                                        <th style="border: 1px solid #ddd;">Employee Code </th>
                                        <th style="border: 1px solid #ddd;">Designation</th>
                                        <th style="border: 1px solid #ddd;">Gross Salary</th>
                                        <th style="border: 1px solid #ddd;">Leave</th>
                                        <th  style="border: 1px solid #ddd;">Total Salary</th>
                                        <th style="border: 1px solid #ddd;">Deduction</th>
                                        <th style="border: 1px solid #ddd;">Incentive / Bonus </th>
                                        <th style="border: 1px solid #ddd;">Transferred salary </th> 
                                        <th style="border: 1px solid #ddd;">Bank Name</th>
                                        <th style="border: 1px solid #ddd;" >Bank A/c No.</th>
                                        <th style="border: 1px solid #ddd;" >IFSC code </th>
                                        <th style="border: 1px solid #ddd;" >SSB A/c No.</th>   
                                        </tr>
                                    </thead> 
                                    <tbody>
                                    @if(count($employee)>0)
                                    <?php 
                                        $total=0;
                                        $totalFinalAmount=0;
                                        $totalCollection=0;
                                        $totalFuleAmount=0;
                                    ?>
                                         @foreach($employee as $index =>  $row) 
                                         <?php
                                            $category = '';
                                            if($row->category==1)
                                            {
                                                $category = 'On-rolled';
                                            }
                                            if($row->category==2)
                                            {
                                               $category = 'Contract'; 
                                            }
                                            $total=$total+$row->salary;
                                         ?>
                                         
                                         <tr>
                                             <td style="border: 1px solid #ddd;">{{ $index+1 }}</td>
                                             <td style="border: 1px solid #ddd;">{{ $category }}</td>
                                             <td style="border: 1px solid #ddd;">{{  $row['branch']->name }}</td>
                                             <td style="border: 1px solid #ddd;">{{  $row['branch']->branch_code }}</td>
                                             <td style="border: 1px solid #ddd;">{{  $row['branch']->sector }}</td>
                                             <td style="border: 1px solid #ddd;">{{  $row['branch']->regan }}</td>
                                             <td style="border: 1px solid #ddd;">{{  $row['branch']->zone }}</td>
                                             
                                             <td style="border: 1px solid #ddd;">{{ $row->employee_name }}</td>
                                             <td style="border: 1px solid #ddd;">{{ $row->employee_code }}</td>
                                             <td style="border: 1px solid #ddd;">{{ getDesignationData('designation_name',$row->designation_id)->designation_name }}</td>
                                             <td style="border: 1px solid #ddd;">
                                                <div class="col-lg-12 error-msg">
                                                  <input type="text" name="salary[]" id="salary_{{$index}}" class="form-control salary "  style="width: 100px" readonly value="{{ number_format((float)$row->salary, 2, '.', '') }}
">
                                                </div> 
                                             </td>
                                             <td style="border: 1px solid #ddd;">
                                                <div class="col-lg-12 error-msg">
                                                  <input type="text" name="leave[]" id="leave_{{$index}}" class="form-control leave "  style="width: 100px" value="0.00">
                                                </div> 
                                             </td>
                                             <td style="border: 1px solid #ddd;">
                                                <div class="col-lg-12 error-msg">
                                                  <input type="text" name="total_salary[]" id="total_salary_{{$index}}" class="form-control  total_salary"  readonly  style="width: 100px" value="{{ number_format((float)$row->salary, 2, '.', '') }}
">
                                                </div> 
                                             </td>
                                             <td style="border: 1px solid #ddd;">
                                                <div class="col-lg-12 error-msg">
                                                  <input type="text" name="deduction[]" id="deduction_{{$index}}" class="form-control  deduction"    style="width: 100px" value="0.00">
                                                </div> 
                                             </td>
                                             
                                             <td style="border: 1px solid #ddd;">
                                                <div class="col-lg-12 error-msg">
                                                  <input type="text" name="incentive_bonus[]" id="incentive_bonus_{{$index}}" class="form-control  incentive_bonus"    style="width: 100px" value="0.00">
                                                </div> 
                                             </td>                                             
                                             <td>
                                                <div class="col-lg-12 error-msg">
                                                  <input type="text" name="transfer_salary[]" id="transfer_salary_{{$index}}" class="form-control  transfer_salary" readonly   style="width: 100px"value="{{ number_format((float)$row->salary, 2, '.', '') }}
">
                                                </div> 
                                             </td>
                                             <td style="border: 1px solid #ddd;">{{ $row->bank_name }}</td>
                                             <td style="border: 1px solid #ddd;"> {{ $row->bank_account_no }}</td>
                                             <td style="border: 1px solid #ddd;">{{ $row->bank_ifsc_code }}</td>
                                             <td style="border: 1px solid #ddd;">{{ $row->ssb_account }}</td>
                                             <input type="hidden" name="employee_id[]"  value="{{ $row->id }}">



                                         </tr>                                      
                                         @endforeach
                                         

                                        <input type="hidden" name="salary_month" id="salary_pre_month" value="{{$pre_month}}">
                                        <input type="hidden" name="salary_month_name" id="salary_pre_month_name" value="{{$pre_month_name}}">
                                        <input type="hidden" name="salary_day" id="salary_day" value="{{$pre_month_days}}">
                                        <input type="hidden" name="salary_year" id="salary_year" value="{{$current_year}}">
                                        <input type="hidden" class="form-control created_at " name="created_at" id="created_at"  >
                          <input type="hidden" class="form-control create_application_date " name="create_application_date" id="create_application_date"  >
                                         <input type="hidden" name="chk" id="chk" value="0">
                                         </tbody>

                                         <tfoot>
                                        <tr>
                                            <td colspan="13" align="right" style="border: 1px solid #ddd;"><strong>Total Amount</strong> </td>
                                            <td colspan="7" align="left" style="border: 1px solid #ddd;"><span id='sum'><strong>{{ number_format((float)$total, 2, '.', '')}}</strong> </span> </td>
                                        </tr>
                                    </tfoot> 


                                         
                                    @else
                                    <tfoot>
                                        <tr>
                                            <td colspan="16" align="center" style="border: 1px solid #ddd;">No Record Found!</td>
                                        </tr>
                                    </tfoot>
                                    @endif
                                    
                                </table>
                            </div>
                            </div>
                             
                 
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12 text-center">
                                @if(count($employee)>0)
                                <button type="button" class=" btn bg-dark legitRipple"  id="submit_transfer" onclick="subSalary();">Salary Generate</button>
                                @endif
                               
                             </div>
                         </div>
                     </div>
                </form> 
                </div>           
        </div>
        

    </div>
</div>
@stop

@section('script')
@include('templates.admin.hr_management.salary.script1')
@stop
