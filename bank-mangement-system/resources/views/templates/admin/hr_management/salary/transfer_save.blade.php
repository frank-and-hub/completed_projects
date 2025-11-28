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
          <form action="#" method="post" enctype="multipart/form-data" id="filter" name="filter">
                        @csrf 
                <input type="hidden" name="amount_mode_exp" class="amount_mode_exp" value="{{$amount_mode}}">
                <input type="hidden" name="selectedRent_exp" class="selectedRent_exp" value="{{$selectedSalary}}">
        </form>

                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">Employee Salary Transfer List - {{$leaserData['company']->name}}</h6>
                        <div class="">
                        
                            <button type="button" class="btn bg-dark legitRipple export ml-2" data-extension="0" style="float: right;">Export xslx</button>
                            
                        </div>
                    </div>
                <form action="{!! route('admin.hr.transfer_save') !!}" method="post" enctype="multipart/form-data"          name="transfer_save" id="transfer_save" class="transfer_save" >
                            @csrf 
                            <input type="hidden" name="created_at" class="created_at">
                            <input type="hidden" name="company_id"  id = "company_id" class="company_id" value = "{{$leaserData['company']->id}}">
                        <div class="card-body">
                        <div class="row">
                           
                            <div class="col-md-12">
                                <div class="table-responsive py-4">
                                <table  class="table table-flush">
                                    <thead>
                                        <tr>                                    
                                        <th style="border: 1px solid #ddd;">S.No</th>
                                        <th style="border: 1px solid #ddd;">BR Name</th>  
                                        <th style="border: 1px solid #ddd;">Employee Name </th>
                                        <th style="border: 1px solid #ddd;">Designation</th>
                                        <th style="border: 1px solid #ddd;">Advance Payment Amount</th>
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
                                            $total_transfer = $total_transfer+(number_format((float)$row->transfer_salary, 2, '.', '') );
                                         ?>
                                         
                                         <tr>
                                             <td style="border: 1px solid #ddd;">{{ $index+1 }}</td> 

                                            <!-- <td style="border: 1px solid #ddd;">{{ $category }}</td>-->
                                             <td style="border: 1px solid #ddd;">{{  $row['salary_branch']->name }}</td>
                                          <!--   <td style="border: 1px solid #ddd;">{{  $row['salary_branch']->branch_code }}</td>
                                             <td style="border: 1px solid #ddd;">{{  $row['salary_branch']->sector }}</td>
                                             <td style="border: 1px solid #ddd;">{{  $row['salary_branch']->regan }}</td>
                                             <td style="border: 1px solid #ddd;">{{  $row['salary_branch']->zone }}</td>-->
                                             <td style="border: 1px solid #ddd;">{{ $row['salary_employee']->employee_name }}</td>
                                          <!--   <td style="border: 1px solid #ddd;">{{ $row['salary_employee']->employee_code }}</td> -->
                                             <td style="border: 1px solid #ddd;">{{ getDesignationData('designation_name',$row['salary_employee']->designation_id)->designation_name}}</td>
                                             <td style="border: 1px solid #ddd;">{{  number_format((float)$row['salary_employee']->advance_payment, 2, '.', '') }}</td>
                                             <td style="border: 1px solid #ddd;">{{  number_format((float)$row->fix_salary, 2, '.', '') }}</td>
                                             <td style="border: 1px solid #ddd;">{{ $row->leave }}</td>
                                             <td style="border: 1px solid #ddd;"> {{ number_format((float)$row->total_salary, 2, '.', '') }}</td>
                                             
                                             <td style="border: 1px solid #ddd;">{{  number_format((float)$row->deduction, 2, '.', '') }}</td>
                                             <td style="border: 1px solid #ddd;">{{  number_format((float)$row->incentive_bonus, 2, '.', '') }}</td>
                                             <td style="border: 1px solid #ddd;">{{ number_format((float)$row->transfer_salary, 2, '.', '') }}</td>
                                             <td style="border: 1px solid #ddd;">{{ $row['salary_employee']->bank_name }}</td>
                                             <td style="border: 1px solid #ddd;"> {{ $row['salary_employee']->bank_account_no }}</td>
                                             <td style="border: 1px solid #ddd;">{{ $row['salary_employee']->bank_ifsc_code }}</td>
                                             
                                             <td style="border: 1px solid #ddd;">{{ $row['salary_employee']->ssb_account }}</td>
                                             <input type="hidden" name="salary_id[]"  value="{{ $row->id }}">  



                                         </tr>                                      
                                         @endforeach
                                             <input type="hidden" name="leaser_id" id="leaser_id" value="{{$leaser_id}}">

                                             <input type="hidden" name="total_transfer" id="total_transfer" value="{{number_format((float)$total_transfer, 2, '.', '')}}">
                                             

                                            <input type="hidden" class="form-control created_at " name="created_at" id="created_at"  >
                                            <input type="hidden" class="form-control create_application_date " name="create_application_date" id="create_application_date"  >

                                         </tbody>
                                         <tfoot>
                                        <tr>
                                            <td colspan="10" align="right" style="border: 1px solid #ddd;"><strong>Total Transfer Amount</strong> </td>
                                            <td colspan="5" align="left" style="border: 1px solid #ddd;"><span ><strong>{{ number_format((float)$total_transfer, 2, '.', '') }}</strong> </span> </td>
                                        </tr>
                                    </tfoot>

                                         
                                    @else
                                    <tfoot>
                                        <tr>
                                            <td colspan="15" align="center" style="border: 1px solid #ddd;">No record </td>
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
                                            <label class="col-form-label col-lg-4">Amount Mode </label>
                                            <div class="col-lg-7 error-msg">
                                                <select class="form-control" id="amount_mode" name="amount_mode">
                                                    <option value="">Select Amount Mode</option>                                                   
                                                    <option value="1" @if($amount_mode==1) selected @endif>SSB</option>                                                
                                                    <option value="2" @if($amount_mode==2) selected @endif>Bank</option> 
                                                    <option value="0" @if($amount_mode==0) selected @endif>Cash</option> 
                                                </select>
                                            </div>
                                        </div>
                                  </div>
                                  
                                  <div class="col-md-6">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-4">Payment Date </label>
                                            <div class="col-lg-7 error-msg">
                                                <div class="input-group">
                                                <input type="text" name="select_date" id="select_date" class="form-control  " >

                                                <input type="hidden" name="ledger_date" id="ledger_date" class="form-control  " readonly value="{{$ledger_date}}"> 

                                               </div>
                                            </div>
                                        </div>
                                  </div>
                                </div>
                                <div class="row branch"  style="display: none;">
                                  <div class="col-md-6">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-4">Branch</label>
                                            <div class="col-lg-7 error-msg">
                                                <select class="form-control" id="payment_branch" name="payment_branch">
                                                  <option value="">Select Branch</option>  
                                                    @foreach( $branch as $branch)
                                                      <option value="{{ $branch->id }}" data-value={{$branch->branch_code}}>{{ $branch->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                  </div>
                                  <div class="col-md-6">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-4">Branch Current Balance</label>
                                            <div class="col-lg-7 error-msg">
                                                 <input class="form-control" id="branch_total_balance" name="branch_total_balance" readonly> 
                                            </div>
                                        </div>
                                  </div>
                                </div>
                                <div class="row bank"  style="display: none;">
                                  <div class="col-md-12">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-2">Payment Mode </label>
                                            <div class="col-lg-7 error-msg">
                                                <select class="form-control" id="payment_mode" name="payment_mode">
                                                    <option value="">Select Payment Mode</option> 
                                                    <option value="1">Cheque</option>
                                                    <option value="2">Online</option>
                                                </select>
                                            </div>
                                        </div>
                                  </div>
                                  <div class="col-md-12 " >
                                    <h6 class="card-title "> Bank Detail</h6>
                                  </div>
                                  <div class="col-md-6 ">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-4">Bank</label>
                                            <div class="col-lg-8 error-msg">
                                                <select class="form-control" id="bank_id" name="bank_id">
                                                    <option value="">Select Bank</option> 
                                                    @foreach ($bank as $val)
                                                      <option value="{{ $val->id }}">{{ $val->bank_name }}</option>
                                                   @endforeach
                                                </select>
                                            </div>
                                        </div>
                                  </div>

                                  <div class="col-lg-6 "> 
                                    <div class="form-group row">
                                      <label class="col-form-label col-lg-4">Account Number<sup class="required">*</sup></label>
                                      <div class="col-lg-8 error-msg">
                                        <select   name="account_id" id="account_id" class="form-control" >  <option value="">Select Account Number</option> 
                                        </select>
                                      </div>
                                    </div>
                                  </div>
                                  <div class="col-lg-6 "> 
                                    <div class="form-group row">
                                      <label class="col-form-label col-lg-4">Bank Balance<sup class="required">*</sup></label>
                                      <div class="col-lg-8 error-msg">
                                        <input type="text" class="form-control" name="bank_balance" id="bank_balance" readonly value="0.00">
                                      </div>
                                    </div>
                                  </div>

                                  <div class="col-md-12 cheque" style="display: none;">
                                    <h6 class="card-title  "> Cheque Detail</h6>
                                  </div>
                                 
                                  
                                  <div class="col-lg-6 cheque" style="display: none;"> 
                                    <div class="form-group row">
                                      <label class="col-form-label col-lg-4">Cheque <sup class="required">*</sup></label>
                                      <div class="col-lg-8 error-msg">
                                        <select   name="cheque_id" id="cheque_id" class="form-control" >  <option value="">Select Cheque</option> 
                                        </select>
                                        
                                      </div>
                                    </div>
                                  </div>
                                  <div class="col-lg-6 cheque_detail" style="display: none;"> 
                                    <div class="form-group row">
                                      <label class="col-form-label col-lg-4">Cheque Number <sup class="required">*</sup></label>
                                      <div class="col-lg-8 error-msg">
                                        <input type="text" class="form-control" name="cheque_number" id="cheque_number" readonly>
                                         
                                      </div>
                                    </div>
                                  </div>
                                  <div class="col-lg-6 cheque_detail" style="display: none;"> 
                                    <div class="form-group row">
                                      <label class="col-form-label col-lg-4">Cheque Amount <sup class="required">*</sup></label>
                                      <div class="col-lg-8 error-msg">
                                        <input type="text" class="form-control" name="cheque_amount" id="cheque_amount" readonly>
                                        
                                      </div>
                                    </div>
                                  </div>
                                  <div class="col-md-12 online" style="display: none;">
                                    <h6 class="card-title  ">Online Detail</h6>
                                  </div>
                                  <div class="col-md-6 online" style="display: none;">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-4"> UTR number / Transaction Number </label>
                                            <div class="col-lg-8 error-msg">
                                                <input type="text" class="form-control" name="utr_tran" id="utr_tran" >
                                            </div>
                                        </div>
                                  </div>
                                  <div class="col-md-6 online" style="display: none;">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-4"> Amount  </label>
                                            <div class="col-lg-8 error-msg">
                                                <input type="text" class="form-control" name="online_tran_amount" id="online_tran_amount" value="{{$total_transfer}}" readonly>
                                            </div>
                                        </div>
                                  </div>
                                  <div class="col-md-6 online" style="display: none;">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-4">RTGS/NEFT Charge   </label>
                                            <div class="col-lg-8 error-msg">
                                                <input type="text" class="form-control" name="neft_charge" id="neft_charge" >
                                            </div>
                                        </div>
                                  </div>
                                  <div class="col-md-6 online" style="display: none;">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-4">Total Amount</label>
                                            <div class="col-lg-8 error-msg">
                                                <input type="text" class="form-control" name="online_total_amount" id="online_total_amount" value="{{number_format((float)$total_transfer, 2, '.', '')}}" readonly> 
                                            </div>
                                        </div>
                                  </div>

                              </div>
                              <div class="row">
                                <div class="col-md-12" >
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-2"><strong>Total  Transfer Amount</strong></label>
                                            <div class="col-lg-8 error-msg">
                                                <input type="text" class="form-control" name="total_transfer_amount" id="total_transfer_amount" value="{{number_format((float)$total_transfer, 2, '.', '')}}" readonly> 
                                            </div>
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
                                <button type="submit" class=" btn bg-dark legitRipple"  id="submit_transfer">Transfer</button>
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
@include('templates.admin.hr_management.salary.transfer_save_script') 
@stop
