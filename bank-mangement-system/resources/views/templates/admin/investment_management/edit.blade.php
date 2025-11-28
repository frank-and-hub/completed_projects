@extends('templates.admin.master')
@section('content')
<div class="loader" style="display: none;"></div>
<div class="content">
  <div class="row">
    <div class="col-md-12">
      <!-- Basic layout-->
      <!-- Validate error messages -->
      @if ($errors->any())
      <div class="alert alert-danger">
        <ul>
          @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
      @endif
      <!-- Validate error messages -->
      <div class="card">
        <div class="card-header header-elements-inline">
          <h3 class="mb-0">Personal Information</h3>

          <div class="header-elements">
            <div class="list-icons">
            </div>
          </div>
        </div>
        <div class="card-body">
          <form action="{{route('admin.investment.update')}}" method="post" id="register-plan" name="register-plan">
            @csrf
            <input type="hidden" class="created_at" name="created_at" id="created_at">
            <input type="hidden" name="memberAutoId" id="memberAutoId" class="form-control" value="{{ $investments['member_id'] }}">
            <input type="hidden" name="formName" id="formName" value="{{ $formName }}">
            <input type="hidden" name="investmentId" id="investmentId" value="{{ $id }}">
            <input type="hidden" name="action" id="action" value="{{ $action }}">
            <input type="hidden" name="ssb_id" id="ssb_id" @if($investments['ssb']) value="{{ $investments['ssb']['id'] }}" @endif>
            <input type="hidden" class="form-control  " name="requestid" id="requestid" readonly value="{{  Request::get('request-id')  }}">




            <div class="form-group row">
              <label class="col-form-label col-lg-2">Customer Id<sup>*</sup></label>
              <div class="col-lg-4">
                {{ $investments->memberCompany->member->member_id }}

              </div>

              <label class="col-form-label col-lg-2">Member Id<sup>*</sup></label>
              <div class="col-lg-4">
                <a href="{{ route('admin.member.edit',['id' => $investments->memberCompany->customer_id ]) }}?type=0">{{ $investments->memberCompany->member_id }}</a>
              </div>
            </div>



            <div class="form-group row">
              <label class="col-form-label col-lg-2">Company<sup>*</sup></label>
              <div class="col-lg-4">
                {{ $investments->company ? $investments->company->name : ''}}

              </div>

              <label class="col-form-label col-lg-2">Branch Name<sup>*</sup></label>
              <div class="col-lg-4">
                <select name="branchid" id="branchid" class="form-control" title="Please select something!" @if($action
                  !='edit' ) disabled="" @endif>
                  <option value="">Select Branch</option>
                  @foreach($branches as $branch)
                  <option value="{{ $branch->id }}" @if($branch->id == $investments['branch_id']) selected @endif>{{ $branch->name }}</option>
                  @endforeach
                </select>
              </div>
            </div>
            {{-- <p> <h3 class="">If you doesn’t know the member id, then click on link </h3><a href="javascript:void(0);" data-toggle="modal" data-target="#modal-form">Query</a></p> --}}

            <div class="form-group row member-detail">
              <label class="col-form-label col-lg-2">First Name<sup class="required">*</sup> </label>
              <div class="col-lg-4">
                <div class="input-group">
                  <input type="text" name="firstname" id="firstname" class="form-control" disabled="" value="@if($investments->memberCompany->member->first_name) {{ $investments->memberCompany->member->first_name }} @else First Name N/A @endif">
                </div>
              </div>

              <label class="col-form-label col-lg-2">Last Name<sup class="required">*</sup> </label>
              <div class="col-lg-4">
                <div class="input-group">
                  <input type="text" name="lastname" id="lastname" class="form-control" disabled="" value="@if($investments->memberCompany->member->last_name) {{ $investments->memberCompany->member->last_name }} @else Last Name N/A @endif">
                </div>
              </div>
            </div>
            <div class="form-group row member-detail">
              <label class="col-form-label col-lg-2">Mobile No.<sup class="required">*</sup> </label>
              <div class="col-lg-4">
                <div class="input-group">
                  <input type="text" name="mobilenumber" id="mobilenumber" class="form-control" disabled="" value="@if($investments->memberCompany->member->mobile_no) {{ $investments->memberCompany->member->mobile_no }} @else Mobile number N/A @endif">
                </div>
              </div>
              <label class="col-form-label col-lg-2">Address<sup class="required">*</sup> </label>
              <div class="col-lg-4">
                <div class="input-group">
                  <input type="text" name="address" id="address" class="form-control" disabled="" value="@if($investments->memberCompany->member->address) {{ $investments->memberCompany->member->address }} @else Address N/A @endif">
                </div>
              </div>
            </div>

            <div class="form-group row member-detail">
              <label class="col-form-label col-lg-2">ID Proof<sup class="required">*</sup> </label>
              <div class="col-lg-4">
                <div class="input-group">
                  <input type="text" name="idproof" id="idproof" class="form-control" disabled="" value="{{ ($idProof) ? $idProof : 'ID Proof N/A' }}">
                </div>
              </div>
              <label class="col-form-label col-lg-2"> Categories<sup class="required">*</sup> </label>
              <div class="col-lg-4">
                <div class="input-group">
                  @php
                  $getSpecialCategory='';
                  if($investments->memberCompany->member->special_category_id>0){

                  $getSpecialCategory = getSpecialCategory($investments->memberCompany->member->special_category_id);
                  }
                  @endphp
                  <input type="text" name="specialcategory" id="specialcategory" class="form-control" disabled="" value="@if($getSpecialCategory) {{ $getSpecialCategory }} @else General Category @endif">
                </div>
              </div>
            </div>

            <div class="form-group row member-detail">
              <label class="col-form-label col-lg-2">Account Number<sup class="required">*</sup> </label>
              <div class="col-lg-4">
                <div class="input-group">
                  <?php //echo "<pre>"; print_r($aDetails); die; ?>
                  <input type="text" name="account_n" id="account_n" class="form-control" value="@if($investments->account_number ) {{ $investments->account_number }} @else Account Number N/A @endif" disabled="">
                </div>
              </div>
              <?php
                  if($investments->plan_id==1)

                  {

                    $ssb = getSsbAccountDetail($investments->account_number);

                    $current_balance = $ssb->balance;

                  }

                  else

                  {

                    $current_balance = $investments->current_balance;

                  }


                ?>
              <label class="col-form-label col-lg-2">Account Balance<sup class="required">*</sup> </label>
              <div class="col-lg-4">
                <div class="input-group">
                  <div class="rupee-img"></div>
                  <input type="text" name="account_b" id="account_b" class="form-control" value="@if($current_balance) {{ $current_balance }} @else Account Balance N/A @endif" disabled="">
                </div>
              </div>
            </div>

            <div class="form-group row">
              <label class="col-form-label col-lg-2">
                <h3 class="associate-member-detail">Agent Details</h3>
              </label>
            </div>
            <div class="form-group row associate-member-detail">
              <label class="col-form-label col-lg-2">Associate ID<sup class="required">*</sup> </label>
              <div class="col-lg-4">
                <div class="input-group">
                  <input type="text" name="associateid" id="associateid" class="form-control" placeholder="Agent Code" value="@if(count($aDetails) > 0) {{ $aDetails[0]->associate_no }} @else Associate ID N/A @endif" disabled="">
                  <input type="hidden" name="associatemid" id="associatemid" value="@if(count($aDetails) > 0) {{ $aDetails[0]->id }} @endif" class="form-control">

                  <input type="hidden" name="hiddenbalance" id="hiddenbalance" class="form-control" value="@if($investments['ssb']) {{ $investments['ssb']['balance'] }} @endif">
                  <input type="hidden" name="hiddenaccount" id="hiddenaccount" class="form-control" value="@if($investments['ssb']) {{ $investments['ssb']['account_no'] }} @endif">
                </div>
              </div>

              <label class="col-form-label col-lg-2">Associate Name<sup class="required">*</sup> </label>
              <div class="col-lg-4">
                <div class="input-group">
                  <input type="text" name="associate_name" id="associate_name" placeholder="Name" class="form-control" value="@if(count($aDetails) > 0) {{ $aDetails[0]->first_name }} @endif" disabled="">
                </div>
              </div>

            </div>
            <div class="form-group row associate-member-detail">
              <label class="col-form-label col-lg-2">Associate Mobile No.<sup class="required">*</sup> </label>
              <div class="col-lg-4">
                <div class="input-group">
                  <input type="text" name="associate_mobile" id="associate_mobile" placeholder="Mobile Number" class="form-control" value="@if(count($aDetails) > 0) {{ $aDetails[0]->mobile_no }} @endif" disabled="">
                </div>
              </div>

              <label class="col-form-label col-lg-2">Associate Carder<sup class="required">*</sup> </label>
              <div class="col-lg-4">
                <div class="input-group">
                  <input type="text" name="associate_carder" id="associate_carder" placeholder="Associate Carder" class="form-control" value="@if(count($aDetails) > 0) {{ $aDetails[0]->carder_name }} @endif" disabled="">
                </div>
              </div>
            </div>

            <div class="form-group row">
              <label class="col-form-label col-lg-2">
                <h3 class="select-plan">Form Details</h3>
              </label>
            </div>
            <div class="form-group row select-plan">
              <label class="col-form-label col-lg-2">Investment Plan<sup>*</sup></label>
              <div class="col-lg-4">
                <select name="investmentplan" id="investmentplan" class="form-control" disabled="">
                  <option>Select Plan</option>
                  @foreach($plans as $plan)
                  <option data-val="{{ $plan->slug }}" value="{{ $plan->id }}" @if($investments['plan_id']==$plan->id) selected @endif>{{ $plan->name }}</option>
                  @endforeach
                </select>
              </div>

              <label class="col-form-label col-lg-2">Form Number<sup>*</sup></label>
              <div class="col-lg-4">
                <input type="text" name="form_number" id="form_number" class="form-control" value="{{ $investments['form_number'] }}" @if($action !='edit' ) readonly="" @endif>
              </div>
            </div>

            <input class="form-control" type="hidden" name="investmentplan" id="investmentplan" value="{{ $investments['plan_id'] }}">
            <input class="form-control" type="hidden" name="plan_type" id="plan_type" value="{{ $investments['plan']->slug }}">
            <input class="form-control" type="hidden" name="hidden_amount" id="hidden_amount" value="{{ $investments['amount'] }}">
            <input class="form-control" type="hidden" name="tenure" value="{{ $investments['tenure'] }}">
            <input class="form-control" type="hidden" name="interest_rate" value="{{ $investments['interest_rate'] }}">

            <input class="form-control" type="hidden" name="hidden_tenure" id="hidden_tenure" value="{{ $investments['tenure'] }}">

            <div class="plan-content-div">
            </div>

            <div class="text-right" @if($action !='edit' ) style="display: none;" @endif>
              <input type="submit" name="submitform" value="Update" class="btn btn-primary">
            </div>

            <div class="modal fade" id="modal-form" tabindex="-1" role="dialog" aria-labelledby="modal-form" aria-hidden="true">
              <div class="modal-dialog modal- modal-dialog-centered modal-sm" role="document">
                <div class="modal-content">
                  <div class="modal-body p-0">
                    <div class="card bg-white border-0 mb-0">
                      <div class="card-header bg-transparent pb-2ß">
                        <div class="text-dark text-center mt-2 mb-3">Enter member name</div>
                      </div>
                      <div class="card-body px-lg-5 py-lg-5">
                        <div class="form-group">
                          <div class="input-group input-group-merge input-group-alternative">
                            <input class="form-control" placeholder="Member Name" type="text" name="member_name" id="member_name" autocomplete="off">
                            <input class="form-control" type="hidden" name="member_id" id="member_id">
                            <div id="suggesstion-box"></div>
                            <label id="name-error" class="member-error error" for="name" style="display: none;">This field is required.</label>
                          </div>
                        </div>
                        <div class="text-right">
                          <button type="button" class="btn btn-primary submitmember" form="modal-details">Submit</button>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </form>
        </div>
      </div>
      <!-- /basic layout -->
    </div>
  </div>
</div>
<script>
  $('#payment-mode').change();
</script>
@stop
@section('script')
@include('templates.admin.investment_management.partials.edit-script')
@stop