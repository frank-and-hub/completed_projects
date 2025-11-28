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
                        <h6 class="card-title font-weight-semibold">Rent  Payble List - {{$leaserData->month_name}} {{$leaserData->year}} </h6>
                    </div>
                    <form action="{!! route('admin.rent.rent_transfer_next') !!}" method="post" enctype="multipart/form-data"  name="rent_transfer" id="rent_transfer" class="rent_transfer" >
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
                                              <input type="checkbox" id="rent_transfer_all" name="rent_transfer_all" class="custom-control-input" value="all" >
                                              <label class="custom-control-label" for="rent_transfer_all" ></label>
                                            </div>
                                          </div> 
                                        </th> 
                                        <th style="border: 1px solid #ddd;">Is Advance</th>  
                                       <th style="border: 1px solid #ddd;">BR Name</th> 
                                        <!-- <th style="border: 1px solid #ddd;">BR Code</th>
                                        <th style="border: 1px solid #ddd;">SO Name</th>
                                        <th style="border: 1px solid #ddd;">RO Name</th>
                                        <th style="border: 1px solid #ddd;">ZO Name</th>-->
                                        <th style="border: 1px solid #ddd;">Rent Type</th>
                                        <!--<th style="border: 1px solid #ddd;">Period From </th>
                                        <th style="border: 1px solid #ddd;">Period To</th>-->
                                        <th style="border: 1px solid #ddd;">Address</th>
                                        <th style="border: 1px solid #ddd;">Owner Name</th>
                                        <!--<th  style="border: 1px solid #ddd;">Owner Mobile Number</th>
                                        <th style="border: 1px solid #ddd;">Owner Pan Card</th>
                                        <th style="border: 1px solid #ddd;">Owner Aadhar Card </th>
                                        <th style="border: 1px solid #ddd;">Owner SSB account </th> 

                                        <th style="border: 1px solid #ddd;"> Owner Bank Name</th>
                                        <th style="border: 1px solid #ddd;" >Owner Bank A/c No.</th>
                                        <th style="border: 1px solid #ddd;" >Owner IFSC code </th>                                      
                                        <th style="border: 1px solid #ddd;">Yearly Increment</th>
                                        <th style="border: 1px solid #ddd;">Office Square feet area</th>-->
                                        <th style="border: 1px solid #ddd;">Advance Payment Amount</th> 
                                        <th style="border: 1px solid #ddd;">Security amount</th> 
                                        <th style="border: 1px solid #ddd;">Rent</th>

                                        <th style="border: 1px solid #ddd;">Transfer Amount</th>

                                       <!-- <th style="border: 1px solid #ddd;">Employee Code</th>-->
                                        <th style="border: 1px solid #ddd;">Employee Name</th>
                                        <th style="border: 1px solid #ddd;">Employee Designation</th>
                                        <th style="border: 1px solid #ddd;">Employee Mobile No.</th>

                                        </tr>

                                        
                                    </thead> 
                                     @if(count($rent_list)>0)
                                    <?php  
                                    $total_transfer=0;
                                    ?>
                                         @foreach($rent_list as $index =>  $row) 
                                         <?php
                                            $branch_id=$row['rentLib']->branch_id;
                                         ?>
                                         
                                         <tr>
                                             <td style="border: 1px solid #ddd;">{{ $index+1 }}</td>
                                             <td style="border: 1px solid #ddd;">
                                               
                                                <div class="col-lg-12 error-msg">
                                                  @if($row['rentLib']->advance_payment>0)
                                                  <a href='{{URL::to("admin/rent/transfer-advance/".$row->id."/".$leaser_id)}}'>
                                                    <div class="custom-control custom-checkbox mb-3 ">
                                                    <label class="custom-control-label" for="rent_transfer_{{$row->id}}" ></label>
                                                  </div>                                                      
                                                  </a>
                                                  @else
                                                  <div class="custom-control custom-checkbox mb-3 ">
    <input type="checkbox" id="rent_transfer_{{$row->id}}" name="rent_transfer[{{$index}}]" class="custom-control-input rent_transfer rent_transferSum" value="{{ $row->transfer_amount }}" >

    <input type="hidden" name="settel_amount[]" id='settel_amount_{{$row->id}}' value="{{$row->settle_amount}}" class="settel_amount">
    <input type="hidden" name="actual_transfer_amount[]" id='actual_transfer_amount_{{$row->id}}' value="{{$row->actual_transfer_amount}}" class="actual_transfer_amount">
                                                    <label class="custom-control-label" for="rent_transfer_{{$row->id}}" ></label>
                                                  </div>
                                                  @endif
                                                </div> 
                                              </td>

                                              <td style="border: 1px solid #ddd;">
                                                @if($row['rentLib']->advance_payment>0)
                                                Yes
                                                @else
                                                No
                                                @endif
                                              </td>
                                             <td style="border: 1px solid #ddd;">{{ $row['rentBranch']->name }}</td>
                                            <!-- <td style="border: 1px solid #ddd;">{{ $row['rentBranch']->branch_code }}</td>
                                             <td style="border: 1px solid #ddd;">{{ $row['rentBranch']->sector }}</td>
                                             <td style="border: 1px solid #ddd;">{{ $row['rentBranch']->regan }}</td>
                                             <td style="border: 1px solid #ddd;">{{ $row['rentBranch']->zone }} </td>-->

                                             <td style="border: 1px solid #ddd;">{{ ($row['rentLib']['AcountHeadCustom']->sub_head) }} </td>
                                           <!--  <td style="border: 1px solid #ddd;"> {{date("d/m/Y", strtotime(convertDate($row['rentLib']->agreement_from)))}} </td>
                                             <td style="border: 1px solid #ddd;">{{date("d/m/Y", strtotime(convertDate($row['rentLib']->agreement_to)))}} </td>-->
                                             <td style="border: 1px solid #ddd;"> {{$row['rentLib']->place}}</td>
                                             <td style="border: 1px solid #ddd;">{{$row['rentLib']->owner_name}} </td>
                                           <!--  <td style="border: 1px solid #ddd;">{{$row['rentLib']->owner_mobile_number}} </td>
                                             <td style="border: 1px solid #ddd;">{{$row['rentLib']->owner_pen_number}}</td>
                                             
                                             <td style="border: 1px solid #ddd;">{{$row['rentLib']->owner_aadhar_number}}</td>
                                             <td style="border: 1px solid #ddd;"> 
                                                @if($row['rentSSB'])
                                                    {{$row['rentSSB']->account_no}}
                                                @endif
                                            </td>
                                             <td style="border: 1px solid #ddd;">{{$row->owner_bank_name}} </td>
                                             <td style="border: 1px solid #ddd;"> {{$row->owner_bank_account_number}}</td>
                                             <td style="border: 1px solid #ddd;"> {{$row->owner_bank_ifsc_code}} </td>
                                             <td style="border: 1px solid #ddd;"> {{number_format((float)$row->yearly_increment, 2, '.', '')}}%</td>
                                             <td style="border: 1px solid #ddd;"> {{$row->office_area}}</td>-->
                                             <td style="border: 1px solid #ddd;"> {{ number_format((float)$row['rentLib']->advance_payment, 2, '.', '')}}</td> 

                                             <td style="border: 1px solid #ddd;">{{number_format((float)$row->security_amount, 2, '.', '')}} </td>
                                             <td style="border: 1px solid #ddd;"> {{number_format((float)$row->rent_amount, 2, '.', '')}}</td>
                                             
                                             <td style="border: 1px solid #ddd;">{{number_format((float)$row->transfer_amount, 2, '.', '')}} </td>
                                             <!--<td style="border: 1px solid #ddd;"> {{$row['rentEmp']->employee_code}} </td>-->
                                             <td style="border: 1px solid #ddd;"> {{$row['rentEmp']->employee_name}} </td>
                                             <td style="border: 1px solid #ddd;"> {{$row['rentEmp']['designation']->designation_name}} </td>
                                             <td style="border: 1px solid #ddd;"> {{$row['rentEmp']->mobile_no}} </td>
                                             <input type="hidden" name="ssb[]"  value="@if($row['rentSSB']) {{$row['rentSSB']->id}} @endif" class="ssb">
                                             <input type="hidden" name="rent_id[]"  value="{{ $row->id }}" class="id_get">
                                              



                                         </tr>                                      
                                         @endforeach
                                         <input type="hidden" name="leaser_id" id="leaser_id" value="{{$leaser_id}}"> 
                                         <input type="hidden" name="select_id"  id='select_id'>
                                         

                                        <input type="hidden" class="form-control created_at " name="created_at" id="created_at"  >
                          <input type="hidden" class="form-control create_application_date " name="create_application_date" id="create_application_date"  >
                                         </tbody>
                                         <tfoot>
                                       
                                        <tr>
                                            <td colspan="10" align="right" style="border: 1px solid #ddd;"><strong>Total Payable Amount</strong> </td>
                                            <td colspan="15" align="left" style="border: 1px solid #ddd;"><span id='total_payble'><strong>0</strong> </span> </td>
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
                                  <div class="col-md-12">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-2">Amount Mode </label>
                                            <div class="col-lg-7 error-msg">
                                                <select class="form-control" id="amount_mode" name="amount_mode">
                                                    <option value="">Select Amount Mode</option> 
                                                    <option value="1">SSB</option>
                                                    <option value="2">Bank</option>
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
                                @if(count($rent_list)>0)
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
@include('templates.admin.rent-management.partials.transfer_script')
@stop
