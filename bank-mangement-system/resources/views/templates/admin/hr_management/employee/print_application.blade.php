@extends('templates.admin.master')

@section('content')
<style type="text/css">
  
</style>

<div class="content" >   
       
      <div class="row" id="application_details"> 
        <table width="100%" border="0" cellspacing="0" cellpadding="0">
          <tr style="display: flex;">
            <td style="padding:10px;width: 60%;">
              <div class="card bg-white" > 
                <div class="card-body">
                  <h3 class="card-title mb-3">Employee Information</h3>
                  <div class="row">
                    <table width="100%" border="0" cellspacing="0" cellpadding="0">
                      <tr>
                        <td style="padding:4px;width: 16%;">Company Name :
                        </td>
                        <td style="padding:4px;width: 34%;">
                        {{ $employee['company']->name}}
                        </td>
                        <td style="padding:4px;width: 16%;">Branch Name: 
                        </td>
                        <td style="padding:4px;width: 34%;">{{ $employee->branch->name}}
                        </td>
                      </tr> 
                      <tr>
                        <td style="padding:4px;width: 16%;">Designation : 
                        </td>
                        <td style="padding:4px;width: 34%;">
                          {{ getDesignationData('designation_name',$employee->designation_id)->designation_name}} 
                        </td>
                        <td style="padding:4px;width: 16%;">Gross Salary : 
                        </td>
                        <td style="padding:4px;width: 34%;">
                          {{  number_format((float)$employee->salary, 2, '.', '')}} <img src='{{url('/')}}/asset/images/rs.png' width='7'>
                        </td>
                      </tr> 

                      

                      <tr>
                        <td style="padding:4px;width: 16%;">ESI Account No: 
                        </td>
                        <td style="padding:4px;width: 34%;">
                        @if($employee->esi_account_no)
                            {{$employee->esi_account_no }}
                          @else
                           N/A
                          @endif
                        </td>
                        <td style="padding:4px;width: 16%;">  UAN/PF  Account No:
                        </td>
                        <td style="padding:4px;width: 34%;">
                            @if($employee->pf_account_no)
                              {{$employee->pf_account_no }}
                            @else
                              N/A
                            @endif
                        </td>
                      </tr>
                      <tr>
                      <td style="padding:4px;width: 16%;">Category :
                        </td>
                        <td style="padding:4px;width: 34%;">
                          @if($employee->category==1)
                              On-rolled
                              @else
                              Contract
                              @endif 
                        </td>
                        <td style="padding:4px;width: 16%;"> Is Customer:
                        </td>
                        <td style="padding:4px;width: 34%;">                  
                          @if($employee->customer_id)
                          Yes
                          @else
                          No
                          @endif
                        </td>
                      </tr>
                      @if($employee->customer_id)
                        <tr>
                          <td style="padding:4px;width: 16%;">Customer Id :
                          </td>
                          <td style="padding:4px;width: 34%;">
                            @if($member)
                            {{ $member->member_id }}
                            @else
                            No
                            @endif
                          </td>
                        </tr>
                        @endif
                    </table>                   
                  </div> 
                </div>
              </div>
              <div class="card bg-white" > 
                <div class="card-body">
                  <h3 class="card-title mb-3">Personal Information </h3>
                  <div class="row">
                    <table width="100%" border="0" cellspacing="0" cellpadding="0">
                      <tr>
                        <td style="padding:4px;width: 44%;">Recommendation Employee Name :
                        </td>
                        <td style="padding:4px;width: 56%;">
                          {{$employee->recommendation_employee_name}}
                        </td> 
                      </tr>

                      <tr>
                        <td style="padding:4px;width: 44%;">Recommendation Employee Designation :
                        </td>
                        <td style="padding:4px;width: 56%;">
                          {{$employee->recom_employee_designation}}
                        </td> 
                      </tr> 
                      <tr>
                        <td style="padding:4px;width: 44%;">Employee Name :
                        </td>
                        <td style="padding:4px;width: 56%;">
                          {{$employee->employee_name}}
                        </td> 
                      </tr>
                      <tr>
                        <td style="padding:4px;width: 44%;">Date of Birth :  
                        </td>
                        <td style="padding:4px;width: 56%;">
                          {{date("d/m/Y", strtotime($employee->dob))}}
                        </td> 
                      </tr>
                      <tr>
                        <td style="padding:4px;width: 44%;">Gender :  
                        </td>
                        <td style="padding:4px;width: 56%;">
                          @if($employee->gender==1)
                            Male
                          @elseif($employee->gender==2)
                            Female
                          @else
                            Other
                          @endif
                        </td> 
                      </tr>
                      <tr>
                        <td style="padding:4px;width: 44%;">Mobile No :  
                        </td>
                        <td style="padding:4px;width: 56%;">
                          {{$employee->mobile_no}}
                        </td> 
                      </tr>
                      <tr>
                        <td style="padding:4px;width: 44%;">Email Id :  
                        </td>
                        <td style="padding:4px;width: 56%;">
                          {{$employee->email}}
                        </td> 
                      </tr>
                      <tr>
                        <td style="padding:4px;width: 44%;">Father/Legal Guardian/Husband's  Name :  
                        </td>
                        <td style="padding:4px;width: 56%;">
                          {{$employee->father_guardian_name}}
                        </td> 
                      </tr>
                      <tr>
                        <td style="padding:4px;width: 44%;">Father/Legal Guardian/Husband's  Number :  
                        </td>
                        <td style="padding:4px;width: 56%;">
                          {{$employee->father_guardian_number}}
                        </td> 
                      </tr>
                      <tr>
                        <td style="padding:4px;width: 44%;">Mother Name :  
                        </td>
                        <td style="padding:4px;width: 56%;">
                          {{$employee->mother_name}}
                        </td> 
                      </tr>
                      <tr>
                        <td style="padding:4px;width: 44%;">Marital status  :  
                        </td>
                        <td style="padding:4px;width: 56%;">
                          @if($employee->marital_status==1)
                            Married 
                          @elseif($employee->marital_status==2)
                            Divorced
                          @else
                            Un Married
                          @endif 
                        </td> 
                      </tr>
                      <tr>
                        <td style="padding:4px;width: 44%;">Employee SSB Account :  
                        </td>
                        <td style="padding:4px;width: 56%;">
                           @if($employee->ssb_account) {{$employee->ssb_account}} @else N/A @endif
                        </td> 
                      </tr>
                      <tr>
                        <td style="padding:4px;width: 44%;">Pan Number :  
                        </td>
                        <td style="padding:4px;width: 56%;">
                          @if($employee->pen_card) {{$employee->pen_card}} @else N/A @endif
                        </td> 
                      </tr>
                      <tr>
                        <td style="padding:4px;width: 44%;">Aadhar Number :  
                        </td>
                        <td style="padding:4px;width: 56%;">
                          @if($employee->aadhar_card) {{$employee->aadhar_card}} @else N/A @endif
                        </td> 
                      </tr>
                      <tr>
                        <td style="padding:4px;width: 44%;">Voter Id Number :  
                        </td>
                        <td style="padding:4px;width: 56%;">
                          @if($employee->voter_id) {{$employee->voter_id}} @else N/A @endif
                        </td> 
                      </tr>
                    </table>                  
                    
                   
                  </div> 
                </div>
              </div>


            </td>
            <td style="padding:10px;width: 40%;">
              <div class="card bg-white" >
                <div class="card-body">
                  <h3 class="card-title mb-3" style="margin-bottom: 0.6rem !important "> Photo  </h3>
                  <div class="row">
                    <div class="col-lg-12 text-center ">                  
                      <span class="text-center rounded-circle w-100">
                        {{--
                        @if($employee->photo=='')
                        <img alt="Image placeholder" id="photo-preview" src="{{url('/')}}/asset/images/user.png" width="150">
                        @else
                        <img alt="Image placeholder" id="photo-preview" src="{{ImageUpload::generatePreSignedUrl('employee/' . $employee->photo)}}" width="150">
                        @endif
                        --}}
                        @if($employee->photo=='')
                          <?php
                            $employee_photo_url = url('/')."/asset/images/user.png";
                          ?>
                        @else
                        <?php
                        $employeefolderName = 'employee/'.$employee->photo;
                        if (ImageUpload::fileExists($employeefolderName) && $employee->photo != '') {
                            $employee_photo_url = ImageUpload::generatePreSignedUrl($employeefolderName);
                        } else {
                            $employee_photo_url = url('/')."/asset/images/user.png";
                        }
                        ?>
                        @endif
                        <img alt="Image placeholder" id="photo-preview" src="{{$employee_photo_url}}" width="150">
                      </span>
                    </div> 
                  </div>
                </div>
              </div> 
              <div class="card bg-white" >            
                <div class="card-body"> 
                  <h3 class="card-title mb-3" style="margin-bottom: 0.6rem !important ">Permanent Address</h3>
                  <div class="row "> 
                    <div class="col-lg-12 ">{{$employee->permanent_address}}
                    </div>
                  </div>

                  <!--<div class=" ">
                    <label class=" col-lg-12">Pin Code : </label>
                    <div class="col-lg-12 ">
                      <input type="text" name="pincode" id="pincode" class="form-control">
                    </div>
                  </div>-->
                </div>
              </div>
              <div class="card" >            
                <div class="card-body">
                  <h3 class="card-title mb-3" style="margin-bottom: 0.6rem !important">Current Address</h3>
                  
                  <div class="row "> 
                    <div class="col-lg-12 ">
                      {{$employee->current_address}}
                    </div>
                  </div>

                  <!--<div class=" ">
                    <label class=" col-lg-12">Pin Code : </label>
                    <div class="col-lg-12 ">
                      <input type="text" name="pincode" id="pincode" class="form-control">
                    </div>
                  </div>-->
                </div>
              </div>
              <div class="card" >            
                <div class="card-body">
                  <h3 class="card-title mb-3" style="margin-bottom: 0.6rem !important " >Language Detail</h3>
                  
                  <table width="100%" border="0" cellspacing="0" cellpadding="0">
                      <tr>
                        <td style="padding:4px;width: 40%;">Language 1 :
                        </td>
                        <td style="padding:4px;width: 60%;">
                          @if($employee->language1) {{$employee->language1}} @else N/A @endif 
                        </td>
                      </tr>
                      <tr>
                        <td style="padding:4px;width: 40%;">Language 2 :
                        </td>
                        <td style="padding:4px;width: 60%;">
                          @if($employee->language2) {{$employee->language2}} @else N/A @endif 
                        </td>
                      </tr>
                      <tr>
                        <td style="padding:4px;width: 40%;">Language 3 :
                        </td>
                        <td style="padding:4px;width: 60%;"> 
                          @if($employee->language3) {{$employee->language3}} @else N/A @endif 
                        </td>
                      </tr>
                    </table>

                </div>
              </div>
            </td>
          </tr>
        </table>
        

        <table width="100%" border="0" cellspacing="0" cellpadding="0">
          <tr>
            <td>
              <div class="card" >            
                <div class="card-body">
                  <h3 class="card-title mb-3" style="margin-bottom: 0.4rem !important ">Bank Account detail </h3>
                  
                  <div class="row ">
                    <table width="100%" border="0" cellspacing="0" cellpadding="0">
                      <tr>
                        <td style="padding:4px;width: 16%;">Bank Name :
                        </td>
                        <td style="padding:4px;width: 34%;">
                          @if($employee->bank_name) {{$employee->bank_name}} @else N/A @endif 
                        </td>
                        <td style="padding:4px;width: 16%;">Account Number : 
                        </td>
                        <td style="padding:4px;width: 34%;"> @if($employee->bank_account_no) {{$employee->bank_account_no}} @else N/A @endif
                        </td>
                      </tr>
                      <tr>
                        <td style="padding:4px;width: 16%;">Bank Address:
                        </td>
                        <td style="padding:4px;width: 34%;">
                          @if($employee->bank_address) {{$employee->bank_address}} @else N/A @endif
                        </td>
                        <td style="padding:4px;width: 16%;">IFSC Code : 
                        </td>
                        <td style="padding:4px;width: 34%;"> @if($employee->bank_ifsc_code) {{$employee->bank_ifsc_code}} @else N/A @endif
                        </td>
                      </tr>
                    </table> 
                  
                     
                  </div>
                 </div>
               </div>
            </td>
          </tr>
        </table>
        <table width="100%" border="0" cellspacing="0" cellpadding="0">
          <tr>
            <td>
              <div class="card bg-white" >            
                <div class="card-body">
                    <h3 class="card-title mb-3"  style="margin-bottom: 0.4rem !important ">Educational Qualification </h3>
                    <div class="table-responsive py-4">
                        <table class="table table-flush" id="qualification" style="margin-bottom: 0.4rem !important ">
                            <thead class="thead-light">
                                    <tr>
                                        <th style="border: 1px solid #ddd; width: 16%">Examination</th>
                                        <th style="border: 1px solid #ddd; width: 12%">Examination Passed</th>
                                        <th style="border: 1px solid #ddd;">School/University Name </th>
                                        <th style="border: 1px solid #ddd;">Subjects</th>
                                        <th style="border: 1px solid #ddd; width: 9%">Division (%)</th> 
                                        <th style="border: 1px solid #ddd; width: 17%">Passing Year </th>  
                                    </tr>
                                </thead> 
                                <tbody>
                                  @foreach($qualification as $val)
                                    <tr>
                                        <td style="border: 1px solid #ddd;padding: 0.5rem 0.1rem;">
                                         
                                          <div class="col-lg-12 ">
                                            {{$val->exam_name}}
                                          </div> 
                                        </td>
                                        <td style="border: 1px solid #ddd;padding: 0.5rem 0.1rem;">
                                          <div class="col-lg-12 ">
                                            {{$val->exam_from}}
                                          </div> 
                                        </td>
                                        <td style="border: 1px solid #ddd;padding: 0.5rem 0.1rem;">
                                            <div class="col-lg-12 ">
                                              {{$val->board_university}}
                                            </div>
                                        </td>
                                        <td style="border: 1px solid #ddd;padding: 0.5rem 0.1rem;">
                                            <div class="col-lg-12 "> 
                                              {{$val->subject}}
                                            </div>
                                        </td>
                                        <td style="border: 1px solid #ddd; padding: 0.5rem 0.1rem;">
                                            <div class="col-lg-12 ">
                                              {{$val->per_division}}
                                            </div>
                                        </td>
                                        <td style="border: 1px solid #ddd;padding: 0.5rem 0.1rem;">
                                            <div class="col-lg-12 ">
                                              {{$val->passing_year}}
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>                   
                            </table>
                        </div>
                </div>
              </div>

            </td>
          </tr>
        </table>

        <table width="100%" border="0" cellspacing="0" cellpadding="0">
          <tr>
            <td>
              <div class="card bg-white" >            
                <div class="card-body">
                    <h3 class="card-title mb-3"  style="margin-bottom: 0.4rem !important "> Diploma </h3>
                    <div class="table-responsive py-4">
                        <table class="table table-flush" id="diploma" style="margin-bottom: 0.4rem !important ">
                            <thead class="thead-light">
                                    <tr>
                                        <th style="border: 1px solid #ddd; width: 16%">Diploma Course</th>
                                        <th style="border: 1px solid #ddd; width: 12%">Academy</th>
                                        <th style="border: 1px solid #ddd;">Institute/University Name </th>
                                        <th style="border: 1px solid #ddd;">Subjects</th>
                                        <th style="border: 1px solid #ddd; width: 9%">Division (%)</th> 
                                        <th style="border: 1px solid #ddd; width: 17%">Passing Year </th>  
                                    </tr>
                                </thead> 
                                <tbody>
                                    @foreach($diploma as $val)
                                    <tr>
                                        <td style="border: 1px solid #ddd;padding: 0.5rem 0.1rem;">
                                         
                                          <div class="col-lg-12 ">
                                            {{$val->course}}
                                          </div> 
                                        </td>
                                        <td style="border: 1px solid #ddd;padding: 0.5rem 0.1rem;">
                                          <div class="col-lg-12 ">
                                            {{$val->academy}}
                                          </div> 
                                        </td>
                                        <td style="border: 1px solid #ddd;padding: 0.5rem 0.1rem;">
                                            <div class="col-lg-12 ">
                                              {{$val->board_university}}
                                            </div>
                                        </td>
                                        <td style="border: 1px solid #ddd;padding: 0.5rem 0.1rem;">
                                            <div class="col-lg-12 "> 
                                              {{$val->subject}}
                                            </div>
                                        </td>
                                        <td style="border: 1px solid #ddd; padding: 0.5rem 0.1rem;">
                                            <div class="col-lg-12 ">
                                              {{$val->per_division}}
                                            </div>
                                        </td>
                                        <td style="border: 1px solid #ddd;padding: 0.5rem 0.1rem;">
                                            <div class="col-lg-12 ">
                                              {{$val->passing_year}}
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>                   
                            </table>
                        </div>
                </div>
              </div>
            </td>
          </tr>
        </table>

        <table width="100%" border="0" cellspacing="0" cellpadding="0">
          <tr>
            <td>
              <div class="card bg-white" >            
                <div class="card-body">
                    <h3 class="card-title mb-3" style="margin-bottom: 0.4rem !important "> Work Experience </h3>
                    <div class="table-responsive py-4">
                        <table class="table table-flush" id="experience" style="margin-bottom: 0.4rem !important ">
                            <thead class="thead-light">
                                    <tr>
                                        <th style="border: 1px solid #ddd;" >Company Name</th>
                                        <th style="border: 1px solid #ddd; width: 21%">Years</th>
                                        <th style="border: 1px solid #ddd;">Nature Of work</th>
                                        <th style="border: 1px solid #ddd; width: 9%">Salary</th>
                                        <th style="border: 1px solid #ddd; width: 25%">Immediate Supervisor Reference </th>    
                                    </tr>
                                </thead> 
                                <tbody>
                                    @foreach($work as $val)
                                    <tr>
                                        <td style="border: 1px solid #ddd;padding: 0.5rem 0.1rem;">
                                         
                                          <div class="col-lg-12 ">
                                            {{$val->company_name}}
                                          </div> 
                                        </td>
                                        <td style="border: 1px solid #ddd;padding: 0.5rem 0.1rem;">
                                          <div class="col-lg-12 ">
                                            From : {{date("d/m/Y", strtotime($val->from_date))}}
                                          </div> 
                                          <div class="col-lg-12 ">
                                            To : {{date("d/m/Y", strtotime($val->to_date))}}
                                          </div>
                                        </td>
                                        <td style="border: 1px solid #ddd;padding: 0.5rem 0.1rem;">
                                            <div class="col-lg-12 ">
                                              {{$val->work_nature}}
                                            </div>
                                        </td>
                                        <td style="border: 1px solid #ddd;padding: 0.5rem 0.1rem;">
                                            <div class="col-lg-12 "> 
                                               {{number_format((float)$val->salary, 2, '.', '')}}<img src='{{url('/')}}/asset/images/rs.png' width='7'>
                                            </div>
                                        </td>
                                        <td style="border: 1px solid #ddd; padding: 0.5rem 0.1rem;">
                                            <div class="col-lg-12 ">
                                              Name : {{$val->supervisor_name}}
                                            </div>
                                            <div class="col-lg-12 ">
                                              No. : {{$val->supervisor_number}}
                                            </div>
                                        </td> 
                                    </tr>
                                    @endforeach
                                </tbody>                   
                            </table>
                        </div>
                </div>
              </div>
            </td>
          </tr>
        </table>
        
      </div>
      <div class="row">
        <div class="col-lg-12">
          <div class="card bg-white" >            
            <div class="card-body ">
                <div class="row">
        <div class="col-lg-6 text-right">
              <button type="submit" class="btn btn-primary" onclick="printDiv('application_details');"> Print<i class="icon-paperplane ml-2" ></i></button>
            </div>
        <div class="col-lg-6 text-left">
              <form action="{!! route('admin.hr.employee_application_export_pdf') !!}" method="post" enctype="multipart/form-data" id="employee_register" name="employee_register"  >
    @csrf
                <input type="hidden" name="id" id="id" value="{{$employee->id}}">
                <button type="submit" class="btn btn-primary"  >Download<i class="icon-download4 ml-2" ></i></button>
              </form>
            </div></div>
            </div>
          </div>
        </div>

      </div>  
</div>

@include('templates.admin.hr_management.employee.script_print')
@stop