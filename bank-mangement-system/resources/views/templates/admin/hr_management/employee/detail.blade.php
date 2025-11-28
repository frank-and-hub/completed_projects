@extends('templates.admin.master')

@section('content')

<div class="content">   
       
      <div class="row">
        @if(isset($_GET['type']))
        <div class="col-lg-12">
          <div class="card bg-white" > 
            <div class="card-body">
              <h3 class="card-title mb-3">@if($application->application_type==2) Resign  @else Register  @endif Application Detail</h3>
              <div class="row">
                <div class="col-lg-4">
                    <div class=" row">
                        <label class=" col-lg-5">Applied Date : </label>
                        <div class="col-lg-7 ">{{date("d/m/Y", strtotime($application->application_date))}}</div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class=" row">
                        <label class=" col-lg-5">Application Status : </label>
                        <div class="col-lg-7 ">
                          @if($application->status==1)
                            Approved
                          @elseif($application->status==3)
                            Rejected
                          @else
                            Pending
                          @endif
                        </div>
                    </div>
                </div>
                @if($application->status==1)
                <div class="col-lg-4">
                    <div class=" row">
                        <label class=" col-lg-6">Approved Date : </label>
                        <div class="col-lg-6 ">{{date("d/m/Y", strtotime($application->status_date))}}</div>
                    </div>
                </div>
                @endif
                @if($application->status==3)
                <div class="col-lg-4">
                    <div class=" row">
                        <label class=" col-lg-5">Reject Date : </label>
                        <div class="col-lg-7 ">{{date("d/m/Y", strtotime($application->status_date))}}</div>
                    </div>
                </div>
                @endif
                @if($application->application_type==2)
                <div class="col-lg-4">
                    <div class=" row">
                        <label class=" col-lg-5">File : </label>
                        <div class="col-lg-7 ">
                          @if($application->resign_file == '')
                            <?php
                              $application_photo_url = url('/')."/asset/images/user.png";
                            ?>
                          @else
                          <?php
                            $applicationfolderName = 'employee/resign/,'.$application->resign_file;
                            if (ImageUpload::fileExists($applicationfolderName) && $application->resign_file != '') {
                                $application_photo_url = ImageUpload::generatePreSignedUrl($applicationfolderName);
                            } else {
                                $application_photo_url = ImageUpload::generatePreSignedUrl('employee/resign/' . $application->resign_file);
                            }
                          ?>
                          @endif
                          <a href="{{$application_photo_url}}"  target='_blank'  >{{$application->resign_file}}</a>
                        </div>
                    </div>
                </div>
                @endif
                @if($application->application_type==2)
                <div class="col-lg-12">
                    <div class=" row">
                        <label class=" col-lg-2">Resign Remark : </label>
                        <div class="col-lg-10 ">{{$application->resign_remark}}</div>
                    </div>
                </div>
                @endif
              </div>
            </div>
          </div>
        </div>
        @endif

        <div class="col-lg-7">
          <div class="card bg-white" > 
            <div class="card-body">
              <h3 class="card-title mb-3">Employee Information</h3>
              <div class="row">
                <div class="col-lg-6">
                    <div class=" row">
                        <label class=" col-lg-5">Company Name : </label>
                        <div class="col-lg-7 ">
                        {{ $employee['company']->name?? 'N/A'}}
                        
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                  <div class=" row">
                      <label class=" col-lg-5">Branch Name : </label>
                      <div class="col-lg-7 ">
                      {{ $employee['branch']->name?? 'N/A'}}
                      </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class=" row">
                        <label class=" col-lg-5">Category : </label>
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
                  <div class=" row">
                      <label class=" col-lg-5">Designation : </label>
                      <div class="col-lg-7 "> 
                        {{ getDesignationData('designation_name',$employee->designation_id)->designation_name}}
                      </div>
                    </div>
                </div>
                <div class="col-lg-6">
                  <div class="row">
                      <label class=" col-lg-5">Gross Salary : </label>
                      <div class="col-lg-7 "> 
                        {{  number_format((float)$employee->salary, 2, '.', '')}} <img src='{{url('/')}}/asset/images/rs.png' width='7'>
                      </div>
                    </div>
                </div>
                <div class="col-lg-6">
                  <div class="row">
                      <label class=" col-lg-5">ESI Account No: </label>
                      <div class="col-lg-7 "> 
                         @if($employee->esi_account_no)
                            {{$employee->esi_account_no }}
                          @else
                           N/A
                          @endif
                      </div>
                    </div>
                </div> 
                <div class="col-lg-6">
                  <div class="row">
                      <label class=" col-lg-5">UAN/PF Account No:  </label>
                      <div class="col-lg-7 "> 
                        @if($employee->pf_account_no)
                            {{$employee->pf_account_no }}
                          @else
                           N/A
                          @endif
                      </div>
                    </div>
                </div>
                <div class="col-lg-6">
                  <div class="row">
                      <label class=" col-lg-5"> Is Customer: </label>
                      <div class="col-lg-7 ">                        
                      @if($employee->customer_id)
                      Yes
                      @else
                      No
                      @endif
                      </div>
                    </div>
                </div>
                @if($employee->customer_id)
                <div class="col-lg-6">
                  <div class="row">
                      <label class=" col-lg-5">Customer ID: </label>
                      <div class="col-lg-7 ">   
                      @if($member)                     
                      {{ $member->member_id }}
                      @endif
                      </div>
                    </div>
                </div>
                @endif
                @if($employee->is_employee ==1)
                <div class="col-lg-6">
                  <div class="row">
                      <label class=" col-lg-5">Employee Code : </label>
                      <div class="col-lg-7 "> 
                        {{$employee->employee_code}}
                      </div>
                    </div>
                </div>
                @endif
                <div class="col-lg-6">
                  <div class="row">
                      <label class=" col-lg-5">Employee  Status: </label>
                      <div class="col-lg-7 "> 
                        @if($employee->status==1)
                            Active 
                          @else
                            Inactive
                          @endif
                      </div>
                    </div>
                </div>
                @if($employee->is_employee ==1)
                <div class="col-lg-6">
                  <div class="row">
                      <label class=" col-lg-5">Is Resign : </label>
                      <div class="col-lg-7 "> 
                        @if($employee->is_resigned==1)
                            Yes 
                          @else
                            No
                          @endif
                      </div>
                    </div>
                </div>
                <div class="col-lg-6">
                  <div class="row">
                      <label class=" col-lg-5">Is Transfer : </label>
                      <div class="col-lg-7 "> 
                        @if($employee->is_transfer==1)
                            Yes 
                          @else
                            No
                          @endif
                      </div>
                    </div>
                </div>
                <div class="col-lg-6">
                  <div class="row">
                      <label class=" col-lg-5">Is Terminate : </label>
                      <div class="col-lg-7 "> 
                        @if($employee->is_terminate==1)
                            Yes 
                          @else
                            No
                          @endif
                      </div>
                    </div>
                </div> 
                
            @endif     
              </div> 
            </div>
          </div>
      
          <div class="card bg-white" >
            <div class="card-body">
              <h3 class="card-title mb-3">Personal Information </h3>
              <div class="row">
                <label class=" col-lg-5">Recommendation Employee Name :  </label>
                <div class="col-lg-7 ">
                  {{$employee->recommendation_employee_name}}
                </div>
              </div>
              <div class="row">
                <label class=" col-lg-5">Recommendation Employee Designation :  </label>
                <div class="col-lg-7 ">
                  {{$employee->recom_employee_designation}}
                </div>
              </div>
              <div class="row">
                <label class=" col-lg-5">Employee Name : </label>
                <div class="col-lg-7 ">
                  {{$employee->employee_name}}
                </div>
              </div>
              <div class="row">
                <label class=" col-lg-5">Date of Birth :  </label>
                <div class="col-lg-7 ">
                  {{date("d/m/Y", strtotime($employee->dob))}}
                </div>
              </div>
              <div class="row">
                <label class=" col-lg-5">Gender :  </label>
                <div class="col-lg-7 ">
                  @if($employee->gender==1)
                    Male
                  @elseif($employee->gender==2)
                    Female
                  @else
                    Other
                  @endif
                </div> 
              </div>
              <div class="row">
                <label class=" col-lg-5">Mobile No : </label>
                <div class="col-lg-7 ">
                  {{$employee->mobile_no}}
                </div>
              </div>              
              <div class="row">
                <label class=" col-lg-5">Email Id :</label>
                <div class="col-lg-7 ">
                  {{$employee->email}}
                </div>
              </div> 
              <div class="row">
                <label class=" col-lg-5">Father/Legal Guardian/Husband's Name   : </label>
                <div class="col-lg-7 ">
                  {{$employee->father_guardian_name}}
                </div>
              </div>
              <div class="row">
                <label class=" col-lg-5">Father/Legal Guardian/Husband's Number   : </label>
                <div class="col-lg-7 ">
                 {{$employee->father_guardian_number}}
                </div>
              </div>
              <div class="row">
                <label class=" col-lg-5">Mother Name : </label>
                <div class="col-lg-7 ">
                  {{$employee->mother_name}}
                </div>
              </div> 
              <div class="row">
                <label class=" col-lg-5">Marital status  : </label>
                <div class="col-lg-7 ">
                  @if($employee->marital_status==1)
                    Married 
                  @elseif($employee->marital_status==2)
                    Divorced
                  @else
                    Un Married
                  @endif 
                </div> 
              </div>
              <div class="row anniversary-date-box">
                <label class=" col-lg-5">Employee SSB Account  : </label>
                <div class="col-lg-7 ">
                  @if($employee->ssb_account) {{$employee->ssb_account}} @else N/A @endif
                </div>
              </div>
              <div class="row anniversary-date-box">
                <label class=" col-lg-5">Pan Number : </label>
                <div class="col-lg-7 ">
                  @if($employee->pen_card) {{$employee->pen_card}} @else N/A @endif
                </div>
              </div>
              <div class="row anniversary-date-box">
                <label class=" col-lg-5">Aadhar Number :  </label>
                <div class="col-lg-7 ">

                  @if($employee->aadhar_card) {{$employee->aadhar_card}} @else N/A @endif
                </div>
              </div>
              <div class="row anniversary-date-box">
                <label class=" col-lg-5">Voter Id Number :  </label>
                <div class="col-lg-7 ">

                  @if($employee->voter_id) {{$employee->voter_id}} @else N/A @endif
                </div>
              </div>




            </div>
          </div>          
        </div>
        <div class="col-lg-5">
          <div class="card bg-white" >
            <div class="card-body">
              <h3 class="card-title mb-3" style="margin-bottom: 0.4rem !important "> Photo  </h3>
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
          <div class="card" >            
            <div class="card-body"> 
              <h3 class="card-title mb-3" style="margin-bottom: 0.4rem !important ">Permanent Address</h3>
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
              <h3 class="card-title mb-3" style="margin-bottom: 0.5rem !important">Current Address</h3>
              
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
              <h3 class="card-title mb-3" style="margin-bottom: 0.4rem !important " >Language Detail</h3>
              
              <div class="row ">
                <label class=" col-lg-4">Language 1 : </label>
                <div class="col-lg-8 ">
                  {{$employee->language1}}
                </div>
              </div>
              <div class=" row">
                <label class=" col-lg-4">Language 2  : </label>
                <div class="col-lg-8 "> 
                  @if($employee->language2) {{$employee->language2}} @else N/A @endif
                </div>
              </div>
              <div class=" row">
                <label class=" col-lg-4">Language 3  : </label>
                <div class="col-lg-8 "> 
                  @if($employee->language3) {{$employee->language3}} @else N/A @endif
                </div>
              </div>

            </div>
          </div>
          <div class="card" >            
            <div class="card-body">
              <h3 class="card-title mb-3" style="margin-bottom: 0.4rem !important ">Bank Account detail </h3>
              
              <div class="row ">
                <label class=" col-lg-4">Bank Name  : </label>
                <div class="col-lg-8 ">
                  @if($employee->bank_name) {{$employee->bank_name}} @else N/A @endif
                </div>
              </div>
              <div class=" row">
                <label class=" col-lg-4">Bank Address  : </label>
                <div class="col-lg-8 ">
                  @if($employee->bank_address) {{$employee->bank_address}} @else N/A @endif
                </div>
              </div>
              <div class="row ">
                <label class=" col-lg-4">Account Number  : </label>
                <div class="col-lg-8 ">
                  @if($employee->bank_account_no) {{$employee->bank_account_no}} @else N/A @endif
                </div>
              </div>
              <div class="row ">
                <label class=" col-lg-4">IFSC Code  : </label>
                <div class="col-lg-8 ">
                  @if($employee->bank_ifsc_code) {{$employee->bank_ifsc_code}} @else N/A @endif
                </div>
              </div>

             </div>
           </div>
        </div>
        @if(($employee->is_resigned==1) && (!isset($_GET['type'])))
        <div class="col-lg-12">
          <div class="card bg-white" >            
            <div class="card-body">
                <h3 class="card-title mb-3"  style="margin-bottom: 0.4rem !important ">Resignation Information </h3>
                <div class="table-responsive py-4">
                    <table class="table table-flush" id="qualification" style="margin-bottom: 0.4rem !important ">
                        <thead class="thead-light">
                                <tr>
                                    <th style="border: 1px solid #ddd; width: 12%">Apply Date</th>
                                    <th style="border: 1px solid #ddd; ">Remark</th>
                                    <th style="border: 1px solid #ddd;width: 15%">File </th> 
                                    <th style="border: 1px solid #ddd;width: 10%">Status </th>
                                    <th style="border: 1px solid #ddd;width: 12%">Approved/Rejected Date </th>
                                </tr>
                            </thead> 
                            <tbody>
                               @if(count($app)>0)
                              @foreach($app as $val)
                                <tr>
                                    <td style="border: 1px solid #ddd;padding: 0.5rem 0.1rem;">  <div class="col-lg-12 ">
                                      {{date("d/m/Y", strtotime($val->application_date))}}
                                       </div>
                                    </td>
                                    <td style="border: 1px solid #ddd;padding: 0.5rem 0.1rem;"><div class="col-lg-12 ">
                                      {{$val->resign_remark}}
                                       </div>
                                    </td>
                                    <td style="border: 1px solid #ddd;padding: 0.5rem 0.1rem;"><div class="col-lg-12 ">
                                      @if($val->resign_file == '')
                                        <?php
                                          $resign_photo_url = url('/')."/asset/images/user.png";
                                        ?>
                                      @else
                                      <?php
                                        $resignfolderName = 'employee/resign/,'.$val->resign_file;
                                        if (ImageUpload::fileExists($resignfolderName) && $val->resign_file != '') {
                                            $resign_photo_url = ImageUpload::generatePreSignedUrl($resignfolderName);
                                        } else {
                                            $resign_photo_url = ImageUpload::generatePreSignedUrl('employee/resign/' . $val->resign_file);
                                        }
                                      ?>
                                      @endif
                                      <a href="{{$resign_photo_url}}"  target='_blank'  >{{$val->resign_file}}</a></div>
                                    </td> 
                                    <td style="border: 1px solid #ddd; padding: 0.5rem 0.1rem;"><div class="col-lg-12 ">
                                          @if($val->status==1)
                                            Approved
                                          @elseif($val->status==3)
                                            Rejected
                                          @else
                                            Pending
                                          @endif</div>
                                    </td>
                                    <td style="border: 1px solid #ddd;padding: 0.5rem 0.1rem;"><div class="col-lg-12 ">
                                          @if($val->status==1)
                                        {{date("d/m/Y", strtotime($val->status_date))}}
                                        @endif
                                      @if($val->status==3)
                                        {{date("d/m/Y", strtotime($val->status_date))}}
                                      @endif
                                    </div>
                                    </td>
                                </tr>
                                @endforeach
                                @else
                                  <tr> <td colspan="6" style="border: 1px solid #ddd;padding: 0.5rem 0.1rem; text-align: center;"> Record not found.</td></tr>
                                @endif
                            </tbody>                   
                        </table>
                    </div>
            </div>
          </div>
        </div>
        @endif


        @if(($employee->is_transfer==1) && (!isset($_GET['type'])))
        <div class="col-lg-12">
          <div class="card bg-white" >            
            <div class="card-body">
                <h3 class="card-title mb-3"  style="margin-bottom: 0.4rem !important ">Transfer Information </h3>
                <div class="table-responsive py-4">
                    <table class="table table-flush" id="qualification" style="margin-bottom: 0.4rem !important ">
                        <thead class="thead-light">
                                <tr>
                                    <th style="border: 1px solid #ddd; ">Branch </th>
                                    <th style="border: 1px solid #ddd; ">Category</th>
                                    <th style="border: 1px solid #ddd;">Designation </th>  
                                    <th style="border: 1px solid #ddd;">Recommendation Name  </th>
                                    <th style="border: 1px solid #ddd; ">Transferred Branch </th>
                                    <th style="border: 1px solid #ddd; ">Transferred Category</th>
                                    <th style="border: 1px solid #ddd;">Transferred Designation </th>  
                                    <th style="border: 1px solid #ddd;">Transferred Recommendation Name</th>
                                    <th style="border: 1px solid #ddd;">Transferred Date </th> 
                                    <th style="border: 1px solid #ddd;">Action</th>
                                </tr>
                            </thead> 
                            <tbody>
                               @if(count($transfer)>0)
                              @foreach($transfer as $val)
                                <tr>
                                    <td style="border: 1px solid #ddd;padding: 0.5rem 0.1rem;">  <div class="col-lg-12 ">
                                      {{ $val['transferBranchOld']->name }}
                                       </div>
                                    </td>
                                    <td style="border: 1px solid #ddd;padding: 0.5rem 0.1rem;"><div class="col-lg-12 ">
                                      <?php $old_category = '';
                                      if($val->old_category==1)
                                      {
                                          $old_category = 'On-rolled';
                                      }
                                      if($val->old_category==2)
                                      {
                                         $old_category = 'Contract'; 
                                      }
                                      ?>
                                      {{ $old_category }}
                                       </div>
                                    </td>
                                    <td style="border: 1px solid #ddd;padding: 0.5rem 0.1rem;">
                                      <div class="col-lg-12 ">
                                        {{ getDesignationData('designation_name',$val->old_designation_id)->designation_name}}
                                      </div>
                                        
                                    </td> 
                                    <td style="border: 1px solid #ddd; padding: 0.5rem 0.1rem;"><div class="col-lg-12 "> {{ $val->old_recommendation_name }} </div>
                                    </td>
                                    <td style="border: 1px solid #ddd;padding: 0.5rem 0.1rem;">  <div class="col-lg-12 ">
                                      {{ $val['transferBranch']->name }}
                                       </div>
                                    </td>
                                    <td style="border: 1px solid #ddd;padding: 0.5rem 0.1rem;"><div class="col-lg-12 ">
                                      <?php $category = '';
                                      if($val->category==1)
                                      {
                                          $category = 'On-rolled';
                                      }
                                      if($val->category==2)
                                      {
                                         $category = 'Contract'; 
                                      }
                                      ?>
                                      {{ $category }}
                                       </div>
                                    </td>
                                    <td style="border: 1px solid #ddd;padding: 0.5rem 0.1rem;">
                                      <div class="col-lg-12 ">
                                        {{ getDesignationData('designation_name',$val->designation_id)->designation_name}}
                                      </div>
                                        
                                    </td> 
                                    <td style="border: 1px solid #ddd; padding: 0.5rem 0.1rem;"><div class="col-lg-12 "> {{ $val->recommendation_name }} </div>
                                    </td>
                                    <td style="border: 1px solid #ddd;padding: 0.5rem 0.1rem;">  <div class="col-lg-12 ">
                                      {{date("d/m/Y", strtotime($val->transfer_date))}}
                                       </div>
                                    </td>

                                     <td style="border: 1px solid #ddd; padding: 0.5rem 0.1rem;"><div class="col-lg-12 "> 
                                      <?php $url7 = URL::to("admin/hr/employee/transfer_letter/".$employee->id); ?><a class="" href="{{$url7}}" title="Transfer Letter"><i class="fas fa-envelope text-default mr-2 "></i></a> 
                                      <?php $url = URL::to("admin/hr/employee/transfer/detail/".$val->id.""); ?>
                                     <a class="" href="{{$url}}" title="Employee Transfer Detail"><i class="fas fa-eye text-default mr-2 "></i></a>
                                    </div>
                                    </td>
                                </tr>
                                @endforeach
                                @else
                                  <tr> <td colspan="10" style="border: 1px solid #ddd;padding: 0.5rem 0.1rem; text-align: center;"> Record not found.</td></tr>
                                @endif
                            </tbody>                   
                        </table>
                    </div>
            </div>
          </div>
        </div>
        @endif

         @if(($employee->is_terminate==1) && (!isset($_GET['type'])))
        <div class="col-lg-12">
          <div class="card bg-white" >            
            <div class="card-body">
                <h3 class="card-title mb-3"  style="margin-bottom: 0.4rem !important ">Termination Information </h3>
                <div class="table-responsive py-4">
                    <table class="table table-flush" id="qualification" style="margin-bottom: 0.4rem !important ">
                        <thead class="thead-light">
                                <tr>
                                    <th style="border: 1px solid #ddd;">Terminate Date</th>
                                    <th style="border: 1px solid #ddd; ">Remark</th> 
                                </tr>
                            </thead> 
                            <tbody>
                               @if(count($terminate)>0)
                              @foreach($terminate as $val)
                                <tr>
                                    <td style="border: 1px solid #ddd;padding: 0.5rem 0.1rem;">  <div class="col-lg-12 ">
                                      {{date("d/m/Y", strtotime($val->terminate_date))}}
                                       </div>
                                    </td>
                                    <td style="border: 1px solid #ddd;padding: 0.5rem 0.1rem;"><div class="col-lg-12 ">
                                      {{$val->remark}}
                                       </div>
                                    </td>
                                   
                                </tr>
                                @endforeach
                                @else
                                  <tr> <td colspan="6" style="border: 1px solid #ddd;padding: 0.5rem 0.1rem; text-align: center;"> Record not found.</td></tr>
                                @endif
                            </tbody>                   
                        </table>
                    </div>
            </div>
          </div>
        </div>
        @endif













        <div class="col-lg-12">
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
                               @if(count($qualification)>0)
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
                                @else
                                  <tr> <td colspan="6" style="border: 1px solid #ddd;padding: 0.5rem 0.1rem; text-align: center;"> Record not found.</td></tr>
                                @endif
                            </tbody>                   
                        </table>
                    </div>
            </div>
          </div>
        </div>
        <div class="col-lg-12">
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
                              @if(count($diploma)>0)
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
                                @else
                                  <tr> <td colspan="6" style="border: 1px solid #ddd;padding: 0.5rem 0.1rem; text-align: center;"> Record not found.</td></tr>
                                @endif
                            </tbody>                   
                        </table>
                    </div>
            </div>
          </div>
        </div>
        <div class="col-lg-12">
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
                              @if(count($work)>0)
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
                                @else
                                  <tr> <td colspan="5" style="border: 1px solid #ddd;padding: 0.5rem 0.1rem; text-align: center;"> Record not found.</td></tr>
                                @endif
                            </tbody>                   
                        </table>
                    </div>
            </div>
          </div>
        </div>


      </div>  
</div>

@stop