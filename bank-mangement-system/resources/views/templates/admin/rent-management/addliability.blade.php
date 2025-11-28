@extends('templates.admin.master')

@section('content')
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                @if ($errors->any())
                  <div class="alert alert-danger">
                      <ul>
                          @foreach ($errors->all() as $error)
                              <li>{{ $error }}</li>
                          @endforeach
                      </ul>
                  </div>
                @endif
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">Branch and Rent type</h6>
                    </div>
                    <form action="{{url('admin/rent/save-liability')}}" method="post" id="add-rent-liability"  enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="created_at" class="created_at">
                        <div class="modal-body">
                            <div class="form-group row">

                               
                                @php
                                $dropDown = $company;
                                $filedTitle = 'Company';
                                $name = 'company_id';
                                @endphp
    
                               @include('templates.GlobalTempletes.new_role_type',['dropDown'=>$dropDown,'filedTitle'=>$filedTitle,'name'=>$name,'value'=>'','multiselect'=>'false','design_type'=>4,'branchShow'=>true,'branchName'=>'branch','apply_col_md'=>true,'multiselect'=>false,'placeHolder1'=>'Please Select Company','placeHolder2'=>'Please Select Branch'])
                                {{-- <div class="col-lg-6">
                                    <label class="col-form-label col-lg-12">Select Branch<sup class="required">*</sup></label>
                                    <div class="col-lg-10">
                                        <select name="branch" id="branch" class="form-control">
                                            <option value=""  >Please Select</option> 
                                            @foreach( $branches as $key => $val )
                                            <option data-val="{{ $val->state_id }}" value="{{ $val->id }}"  >{{ $val->name }}</option> 
                                            @endforeach
                                        </select>
                                    </div>
                                </div> --}}

                                

                                <div class="col-lg-6">
                                    <label class="col-form-label col-lg-12">Select Rent Type<sup class="required">*</sup></label>
                                    <div class="col-lg-10">
                                        <select name="rentType" id="rentType" class="form-control">
                                            <option value="">--- Please Select ---</option> 
                                            @foreach($accountHeadLibilities as $key => $val )
                                            <option value="{{ $val->head_id }}"  >{{ $val->sub_head }}</option> 
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <label class="col-form-label col-lg-12">Register Date<sup class="required">*</sup></label>
                                    <div class="col-lg-10">
                                                <input type="text" name="select_date" id="select_date" class="form-control  " readonly title="Please select the date">

                                                <input type="hidden" name="create_application_date" id="create_application_date" class="form-control  create_application_date" readonly >

                                    </div>
                                </div>

                                <div class="col-lg-12 mt-4" style="margin-top: 10px;">
                                    <h6 class="card-title font-weight-semibold">Agreement Period</h6>
                                </div>

                                <div class="col-lg-6">
                                    <label class="col-form-label col-lg-12">From<sup class="required">*</sup></label>
                                    <div class="col-lg-10">
                                        <input type="text" name="agreement_from" id="agreement_from" class="form-control cal-date" readonly title="Please select the date">
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <label class="col-form-label col-lg-12">To<sup class="required">*</sup></label>
                                    <div class="col-lg-10">
                                        <input type="text" name="agreement_to" id="agreement_to" class="form-control cal-date" readonly title="Please select the date">
                                    </div>
                                </div>

                                <!-- <div class="col-lg-6">
                                    <label class="col-form-label col-lg-12">Select Date<sup class="required">*</sup></label>
                                    <div class="col-lg-10">
                                        <input type="text" name="date" id="date" class="form-control cal-date">
                                    </div>
                                </div> -->

                                <div class="col-lg-6">
                                    <label class="col-form-label col-lg-12">Place<sup class="required">*</sup></label>
                                    <div class="col-lg-10">
                                        <input type="text" name="place" id="place" class="form-control">
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <label class="col-form-label col-lg-12">Owner Name<sup class="required">*</sup></label>
                                    <div class="col-lg-10">
                                        <input type="text" name="owner_name" id="owner_name" class="form-control">
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <label class="col-form-label col-lg-12">Owner Mobile Number <sup class="required">*</sup></label>
                                    <div class="col-lg-10">
                                        <input type="text" name="owner_mobile_number" id="owner_mobile_number" class="form-control">
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <label class="col-form-label col-lg-12">Owner Pan Card<sup class="required">*</sup></label>
                                    <div class="col-lg-10">
                                        <input type="text" name="owner_pen_card" id="owner_pen_card" class="form-control">
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <label class="col-form-label col-lg-12">Owner Aadhar Card <sup class="required">*</sup></label>
                                    <div class="col-lg-10">
                                        <input type="text" name="owner_aadhar_card" id="owner_aadhar_card" class="form-control">
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <label class="col-form-label col-lg-12">Owner SSB account</label>
                                    <div class="col-lg-10">
                                        <input type="text" name="owner_ssb_account" id="owner_ssb_account" class="form-control">
                                        <input type="hidden" name="owner_ssb_id" id="owner_ssb_id" class="form-control"  >
                                        
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <label class="col-form-label col-lg-12">Owner SSB account Date</label>
                                    <div class="col-lg-10">
                                        <input type="text" name="ssb_date" id="ssb_date" class="form-control" readonly> 
                                        
                                    </div>
                                </div>

                                <div class="col-lg-12 mt-4" style="margin-top: 10px;">
                                    <h6 class="card-title font-weight-semibold">Owner bank detail </h6>
                                </div>

                                <div class="col-lg-6">
                                    <label class="col-form-label col-lg-12">Owner Bank name<sup class="required">*</sup></label>
                                    <div class="col-lg-10">
                                        <input type="text" name="bank_name" id="bank_name" class="form-control">
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <label class="col-form-label col-lg-12">Owner Bank account Number <sup class="required">*</sup></label> 
                                    <div class="col-lg-10">
                                        <input type="text" name="bank_account_number" id="bank_account_number" class="form-control">
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <label class="col-form-label col-lg-12">Owner IFSC code <sup class="required">*</sup></label>
                                    <div class="col-lg-10">
                                        <input type="text" name="ifsc_code" id="ifsc_code" class="form-control">
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <label class="col-form-label col-lg-12">Security amount <sup class="required">*</sup></label>
                                    <div class="col-lg-10">
                                        <div class="rupee-img"></div>
                                        <input type="text" name="security_amount" id="security_amount" class="form-control rupee-txt">
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <label class="col-form-label col-lg-12">Rent <sup class="required">*</sup></label>
                                    <div class="col-lg-10">
                                        <div class="rupee-img"></div>
                                        <input type="text" name="rent" id="rent" class="form-control rupee-txt">
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <label class="col-form-label col-lg-12">Yearly Increment in %  <sup class="required">*</sup></label>
                                    <div class="col-lg-10">
                                        <input type="text" name="yearly_increment" id="yearly_increment" class="form-control">
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <label class="col-form-label col-lg-12">Office Square feet area <sup class="required">*</sup></label>
                                    <div class="col-lg-10">
                                        <input type="text" name="office_area" id="office_area" class="form-control">
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <label class="col-form-label col-lg-12">Employee Code <sup class="required">*</sup></label>
                                    <div class="col-lg-10">
                                        <input type="text" name="employee_code" id="employee_code" class="form-control">
                                        <input type="hidden" name="employee_id" id="employee_id" class="form-control">

                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <label class="col-form-label col-lg-12">Employee Date <sup class="required">*</sup></label>
                                    <div class="col-lg-10">
                                        <input type="text" name="employee_date" id="employee_date" class="form-control" readonly>                                           
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <label class="col-form-label col-lg-12">Authorized Employee name <sup class="required">*</sup></label>
                                    <div class="col-lg-10">
                                        <input type="text" name="employee_name" id="employee_name" class="form-control" readonly>
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <label class="col-form-label col-lg-12">Authorized Employee Designation <sup class="required">*</sup></label>
                                    <div class="col-lg-10">
                                        <input type="text" name="employee_designation" id="employee_designation" class="form-control" readonly>
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <label class="col-form-label col-lg-12">Mobile Number<sup class="required">*</sup></label>
                                    <div class="col-lg-10">
                                        <input type="text" name="mobile_number" id="mobile_number" class="form-control" readonly>
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <label class="col-form-label col-lg-12">Rent Agreement<sup class="required">*</sup></label>
                                    <div class="col-lg-10">
                                        <input type="file" name="rent_agreement" id="rent_agreement" class="form-control" required>
                                    </div>
                                </div>

                            </div>
                        </div>
                        <div class="modal-footer">
                            <a href="{{ url()->previous() }}" type="button" class="btn btn-link" data-dismiss="modal">Back</a>
                            <button type="submit" class="btn bg-dark">Submit<i class="icon-paperplane ml-2"></i></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@include('templates.admin.rent-management.partials.script')
<script>
        $('#company_id,#branch').parents('.col-md-4').addClass('col-md-6').removeClass('col-md-4');
        $('#company_id,#branch').closest('.row').removeClass('row');
    $('#company_id,#branch').parents('.col-lg-12').addClass('col-lg-10').removeClass('col-lg-12');
</script>
@stop
