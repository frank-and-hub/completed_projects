@extends('templates.admin.master')

@section('content')
 
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
        <div class="col-md-12" >
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">Employee Salary List - @if(isset($leaserData->month_name)){{$leaserData->month_name}}@endif @if(isset($leaserData->year)){{$leaserData->year}}@endif- {{$leaserData->company->name}}</h6>
                    </div>
                    <form action="{!! route('admin.hr.transfer_next') !!}" method="post" enctype="multipart/form-data"  name="salary_transfer" id="salary_transfer" class="salary_transfer" >
                        @csrf 
                        <input type="hidden" name="created_at" class="created_at">
                    <div class="card-body">
                        <div class="row">
                           
                            <div class="col-md-12">
                                <div class="table-responsive py-4">
                                    <table  class="table table-flush  table-striped">
                                        <thead>
                                            <tr>                                    
                                            <th style="border: 1px solid #ddd;">S.No</th>
                                            <th style="border: 1px solid #ddd;">
                                            <div class="col-lg-12"> 
                                                
                                                <div class="custom-control custom-checkbox mb-3 ">
                                                <input type="checkbox" id="salary_transfer_all" name="salary_transfer_all" class="custom-control-input" value="all" >
                                                <label class="custom-control-label" for="salary_transfer_all" ></label>
                                                </div>
                                            </div> 
                                            </th>
                                            <th style="border: 1px solid #ddd;">Is Advance</th>
                                            <th style="border: 1px solid #ddd;">Part Payment</th> 
                                            <!--<th style="border: 1px solid #ddd;">Category</th> -->
                                            <th style="border: 1px solid #ddd;">BR Name</th> 
                                            <!--<th style="border: 1px solid #ddd;">BR Code</th>
                                            <th style="border: 1px solid #ddd;">SO Name</th>
                                            <th style="border: 1px solid #ddd;">RO Name</th>
                                            <th style="border: 1px solid #ddd;">ZO Name</th>-->
                                            <th style="border: 1px solid #ddd;">Employee Name </th>
                                            <!--<th style="border: 1px solid #ddd;">Employee Code </th>-->
                                            <th style="border: 1px solid #ddd;">Designation</th>
                                            <th style="border: 1px solid #ddd;">Advance Payment Amount</th> 
                                            <th style="border: 1px solid #ddd;">Fix Salary</th>
                                            <th style="border: 1px solid #ddd;">Leave</th>
                                            <th  style="border: 1px solid #ddd;">Total Salary</th>
                                            <th style="border: 1px solid #ddd;">Deduction</th>
                                            <th style="border: 1px solid #ddd;">Incentive / Bonus </th>

                                            <th style="border: 1px solid #ddd;">Payable Amount </th>
                                            <th style="border: 1px solid #ddd;">ESI Amount </th>
                                            <th style="border: 1px solid #ddd;">PF Amount </th>
                                            <th style="border: 1px solid #ddd;">TDS Amount </th> 


                                            <th style="border: 1px solid #ddd;">Final Payable Salary </th> 
                                            <th style="border: 1px solid #ddd;">Due Amount </th> 
                                            <th style="border: 1px solid #ddd;">Bank Name</th>
                                            <th style="border: 1px solid #ddd;" >Bank A/c No.</th>
                                            <th style="border: 1px solid #ddd;" >IFSC code </th>
                                            <th style="border: 1px solid #ddd;" >SSB A/c No.</th>   
                                            </tr>
                                        </thead> 
                                        <tbody>
                                        @if(count($salary_list)>0)
                                        <?php  
                                        $total_transfer=0;
                                        ?>
                                            @foreach($salary_list as $index =>  $row) 
                                            <?php
                                                $category = '';
                                                if($row['salary_employee']->category==1)
                                                {
                                                    $category = 'On-rolled';
                                                }
                                                if($row['salary_employee']->category==2)
                                                {
                                                $category = 'Contract'; 
                                                }
                                                $due=$row->actual_transfer_amount-$row->transferred_salary;
                                                $total_transfer = $total_transfer+(number_format((float)$due, 2, '.', '') );
                                                $adv = (count($row['advance']) > 0) ? 'yes' : 'no';
                                                if($adv == 'yes'){
                                                $sum_adv = 0;
                                                foreach($row['advance'] as $item){
                                                    $sum_adv += $item['settle_amount'];
                                                }}else{
                                                    $sum_adv = 0;
                                                }
                                            ?>
                                            
                                            
                                            <tr>
                                                <td style="border: 1px solid #ddd;">{{ $index+1 }}</td>
                                                <td style="border: 1px solid #ddd;">
                                                    @if($row['salary_employee']->advance_payment>0 && $adv == 'yes')
                                                    <a href='{{URL::to("admin/hr/salary/transfer-advance/".$row->id."/".$leaser_id)}}' target="_blank">
                                                        <div class="custom-control custom-checkbox mb-3 ">
                                                        <label class="custom-control-label" for="salary_transfer_{{$row->id}}" ></label>
                                                    </div>                                                      
                                                    </a>
                                                    @else                                                   
                                                    <div class="col-lg-12 error-msg">
                                                    
                                                    <div class="custom-control custom-checkbox mb-3 ">
                                                        <input type="checkbox" id="salary_transfer_{{$row->id}}" name="salary_transfer[{{$index}}]" class="custom-control-input salary_transfer salary_transferSum" value="{{ number_format((float)$due, 2, '.', '')}}"  data-branch="{{$row['salary_branch']->id}}" data-id = "{{$row->id}}">
                                                        <label class="custom-control-label" for="salary_transfer_{{$row->id}}" ></label>
                                                    </div>
                                                    </div> 
                                                    @endif
                                                </td>
                                                <td style="border: 1px solid #ddd;">
                                                    @if($row['salary_employee']->advance_payment>0 && $adv == 'yes')
                                                    Yes
                                                    @else
                                                    No
                                                    @endif
                                                   
                                                </td>
                                                <td style="border: 1px solid #ddd;">
                                                    <a href='{{URL::to("admin/salary/part-payment/".$row->id."/".$leaser_id)}}'>
                                                        Part Payment                                                     
                                                    </a>                                            
                                                </td>

                                                <!-- <td style="border: 1px solid #ddd;">{{ $category }}</td>-->
                                                <td style="border: 1px solid #ddd;">{{  $row['salary_branch']->name }}</td>
                                                <input type="hidden" name="branch_name[]"  value="{{ $row['salary_branch']->name }}" class="branch_name">
                                            <!--     <td style="border: 1px solid #ddd;">{{  $row['salary_branch']->branch_code }}</td>
                                            <td style="border: 1px solid #ddd;">{{  $row['salary_branch']->sector }}</td>
                                                <td style="border: 1px solid #ddd;">{{  $row['salary_branch']->regan }}</td>
                                                <td style="border: 1px solid #ddd;">{{  $row['salary_branch']->zone }}</td>-->
                                                <td style="border: 1px solid #ddd;">{{ $row['salary_employee']->employee_name }}</td>
                                                <!-- <td style="border: 1px solid #ddd;">{{ $row['salary_employee']->employee_code }}</td>-->
                                                <td style="border: 1px solid #ddd;">{{ getDesignationData('designation_name',$row['salary_employee']->designation_id)->designation_name}}</td>
                                                <td style="border: 1px solid #ddd;">{{  number_format((float)$sum_adv, 2, '.', '') }}</td>
                                                <td style="border: 1px solid #ddd;">{{  number_format((float)$row->fix_salary, 2, '.', '') }}</td>
                                                <td style="border: 1px solid #ddd;">{{ $row->leave }}</td>
                                                <td style="border: 1px solid #ddd;"> {{ number_format((float)$row->total_salary, 2, '.', '') }}</td>
                                                
                                                <td style="border: 1px solid #ddd;">{{  number_format((float)$row->deduction, 2, '.', '') }}</td>
                                                <td style="border: 1px solid #ddd;">{{  number_format((float)$row->incentive_bonus, 2, '.', '') }}</td>
                                                
                                                <td style="border: 1px solid #ddd;">{{  number_format((float)$row->paybale_amount, 2, '.', '') }}</td>
                                                <td style="border: 1px solid #ddd;">{{  number_format((float)$row->esi_amount, 2, '.', '') }}</td>
                                                <td style="border: 1px solid #ddd;">{{  number_format((float)$row->pf_amount, 2, '.', '') }}</td>
                                                <td style="border: 1px solid #ddd;">{{  number_format((float)$row->tds_amount, 2, '.', '') }}</td>
                                                
                                                
                                                <td style="border: 1px solid #ddd;">{{ number_format((float)$row->actual_transfer_amount, 2, '.', '') }}</td>
                                                <td style="border: 1px solid #ddd;">{{ number_format((float)$row->actual_transfer_amount-$row->transferred_salary, 2, '.', '') }}</td>
                                                <td style="border: 1px solid #ddd;">{{ $row['salary_employee']->bank_name }}</td>
                                                <td style="border: 1px solid #ddd;"> {{ $row['salary_employee']->bank_account_no }}</td>
                                                <td style="border: 1px solid #ddd;">{{ $row['salary_employee']->bank_ifsc_code }}</td>
                                                
                                                <td style="border: 1px solid #ddd;">{{ $row['salary_employee']->ssb_account }}</td>
                                                <input type="hidden" name="ssb[]"  value="{{ $row['salary_employee']->ssb_account }}" class="ssb">
                                                <input type="hidden" name="salary_id[]"  value="{{ $row->id }}" class="id_get">
                                                



                                            </tr>                                      
                                            @endforeach
                                            <input type="hidden" name="leaser_id" id="leaser_id" value="{{$leaser_id}}"> 
                                            <input type="hidden" name="select_id"  id='select_id'>
                                            

                                            <input type="hidden" class="form-control created_at " name="created_at" id="created_at"  >
                                            <input type="hidden" class="form-control create_application_date " name="create_application_date" id="create_application_date"  >
                                            </tbody>
                                            <tfoot>
                                            <tr>
                                                <td colspan="12" align="right" style="border: 1px solid #ddd;"><strong>Total Transfer Amount</strong> </td>
                                                <td colspan="10" align="left" style="border: 1px solid #ddd;"><span id='total_transfer_amount'><strong>0.00</strong> </span> </td>
                                            </tr>
                                        </tfoot>

                                            
                                        @else
                                        <tfoot>
                                            <tr>
                                                <td colspan="22" align="center" style="border: 1px solid #ddd;">No record </td>
                                            </tr>
                                        </tfoot>
                                        @endif
                                        
                                    </table>
                                </div>
                            </div>
                            <div class="col-md-12" style="padding-top: 30px">
                                <div class="row">
                                    <div class="col-md-12 cheque" >
                                        <h6 class="card-title font-weight-semibold"> Payment Detail</h6>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-2">Amount Mode </label>
                                            <div class="col-lg-7 error-msg">
                                                <select class="form-control" id="amount_mode" name="amount_mode">
                                                    <option value="">Select Amount Mode</option> 
                                                    <option value="1">SSB</option>
                                                    <option value="2">Bank</option>
                                                    <option value="0">Cash</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                     
                                    <div class="col-lg-6">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-4">Select Branch <sup class="required">*</sup></label>
                                            <div class="col-lg-8 error-msg">
                                                <select name="branch" id="branch" class="form-control">
                                                <option value=""  >Please Select Branch</option> 
                                                   
                                                    @foreach ($branch as $val)
                                                    <option value="{{ $val['branch']['id'] }}">{{ $val['branch']['name'] }}</option>
                                                 @endforeach
                                                </select>
                                            </div>
                                        </div>

                                    </div>

                                    

                                </div> 

                            </div>
                             
                 
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12 text-center">
                                @if(count($salary_list)>0)
                                <button type="submit" class=" btn bg-dark legitRipple"  id="submit_transfer">Next</button>
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
@include('templates.admin.hr_management.salary.transfer_script') 
@stop
