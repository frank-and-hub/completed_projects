@extends('layouts/branch.dashboard')

@section('content')

<div class="loader" style="display: none;"></div>

<div class="container-fluid mt--6">
  <div class="content-wrapper">
    <div class="row">
      <div class="col-lg-12">
        <div class="card bg-white">
          <div class="card-body page-title">
              <h3 class="">{{$title}}</h3>
              <a href="{!! route('investment.plans') !!}" style="float:right" class="btn btn-secondary">Back</a>
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
          </div>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-md-12">
        <!-- Basic layout-->
        <div class="card">
          <div class="card-header header-elements-inline">
            <h3 class="mb-0">Registration </h3>
                <div class="header-elements">
                  <div class="list-icons">
                </div>
              </div>
          </div>
          <div class="card-body">
            <form action="{{route('branch.investment.store')}}" method="post" id="register-plan" name="register-plan">
            @csrf
            <input type="hidden" name="create_application_date" id="created_at" value="">
                <div class="form-group row">
                  <label class="col-form-label col-lg-2">Customer Id<sup>*</sup></label>
                  <div class="col-lg-12">
                    <input type="text" name="memberid" id="memberid" class="form-control" placeholder="Customer  Id" required="" autocomplete="off">
                    <input type="hidden" name="memberAutoId" id="memberAutoId" class="form-control">
                    <input type="hidden" name="stationary_charge" id="stationary-charge">
                    <input type="hidden" name="branch_id" id="branch_id" value ="{{ getBranchDetailManagerId(Auth::user()->id)->id}}">
                    <input type="hidden" name="branchid" id="branchid" value ="{{ getBranchDetailManagerId(Auth::user()->id)->id}}">
                    <input type="hidden" name="company_id" id="company_id" >
                    <input type="hidden" name="newUser" id="newUser">
                    <input type="hidden" name="member_company_id" id="member_company_id">
                    <input type="hidden" name="plan_sub_category" id="plan_sub_category" >

            <input type="hidden" name="is_ssb_required" id="is_ssb_required" >

                  </div>
                </div>    
                <p><h3 class="">If you doesn’t know the member id, then click on link </h3><a href="javascript:void(0);" data-toggle="modal" data-target="#modal-form">Query</a></p>

                <div class="form-group row member-detail" style="display: none;">
                  <div class="col-lg-4">
                    <div class="input-group">
                      <input type="text" name="firstname" id="firstname" class="form-control" disabled="">
                    </div>
                  </div>
                  <div class="col-lg-4">
                    <div class="input-group">
                      <input type="text" name="lastname" id="lastname" class="form-control" disabled="">
                    </div>
                  </div>
                  <div class="col-lg-4">
                    <div class="input-group">
                      <input type="text" name="mobilenumber" id="mobilenumber" class="form-control" disabled="">
                    </div>
                  </div>
                </div>

                <div class="form-group row member-detail" style="display: none;">
                  <div class="col-lg-4">
                    <div class="input-group">
                      <input type="text" name="address" id="address" class="form-control" disabled="">
                    </div>
                  </div>
                  <div class="col-lg-4">
                    <div class="input-group">
                      <input type="text" name="idproof" id="idproof" class="form-control" disabled="">
                    </div>
                  </div>
                  <div class="col-lg-4">
                    <div class="input-group">
                      <input type="text" name="specialcategory" id="specialcategory" class="form-control" disabled="">
                    </div>
                  </div>
                </div>

                <div class="form-group row member-detail" style="display: none;">
                  <div class="col-lg-4">
                    <div class="input-group">
                      <input type="text" name="associateid" id="associateid" class="form-control" placeholder="Agent Code" required="" autocomplete="off">
                      <input type="hidden" name="associatemid" id="associatemid" class="form-control">
                    </div>
                  </div>
                  <input type="hidden" name="hiddenbalance" id="hiddenbalance" class="form-control" value="">
                  <input type="hidden" name="hiddenaccount" id="hiddenaccount" class="form-control" value="">
                </div>

               <!--  <div class="form-group row member-not-found" style="display: none;">
                  <div class="col-lg-12">
                    <div class="input-group">
                      <div class="alert alert-danger alert-block">  <strong>Member not found</strong> </div>
                    </div>
                  </div>
                </div> -->
                <div class="alert alert-danger alert-block member-not-found" style="display: none;">  <strong id="member_not_found">Member not found</strong> </div>

                <h3 class="associate-member-detail" style="display: none;">Agent Details</h3>
                <div class="form-group row associate-member-detail" style="display: none;">
                  <div class="col-lg-4">
                    <div class="input-group">
                      <input type="text" name="associate_name" id="associate_name" placeholder="Name" class="form-control" disabled="">
                    </div>
                  </div>
                  <div class="col-lg-4">
                    <div class="input-group">
                      <input type="text" name="associate_mobile" id="associate_mobile" placeholder="Mobile Number" class="form-control" disabled="">
                    </div>
                  </div>
                  <div class="col-lg-4">
                    <div class="input-group">
                      <input type="text" name="associate_carder" id="associate_carder" placeholder="Associate Carder" class="form-control" disabled="">
                    </div>
                  </div>
                </div>

                <div class="alert alert-danger alert-block associate-not-found" style="display: none;">  <strong>Agent not found</strong> </div>

                <h3 class="select-plan" style="display: none;">Form Details</h3>
                <span class='mi-charge text-danger info' style="display: none;">Note:<sup>*</sup> MI Charge:10/- & STN Charge:90/- will be applicable.</span> <br/>

                <h4 class="stationary-charge" style="display: none;">Stationary Charges : 50/-</h4>
                <div class="form-group row select-plan" style="display: none;">
                  <label class="col-form-label col-lg-2">Investment Plan<sup>*</sup></label>
                  <div class="col-lg-4">
                    <select name="investmentplan" id="investmentplan" class="form-control" title="Please select something!">
                      <option value="">Select Plan</option>
                      @foreach($plans as $plan)
                        <option data-val="{{ $plan->slug }}" data-category="{{$plan->plan_category_code}}" data-min = "{{$plan->min_deposit}}" data-max = "{{$plan->max_deposit}}"  data-multiple = "{{$plan->multiple_deposit}}"
                         data-company="{{$plan->company_id}}" data-ssb-required="{{$plan->is_ssb_required}}" value="{{ $plan->id }}" data-sub-category="{{$plan->plan_sub_category_code}}">{{ $plan->name }}</option>
                      @endforeach
                    </select>
                  </div>

                  <label class="col-form-label col-lg-2">Form Number<sup>*</sup></label>
                  <div class="col-lg-4">
                    <input type="text" name="form_number" id="form_number" class="form-control">
                  </div>
                </div> 

                <input class="form-control" type="hidden" name="plan_type" id="plan_type">
                <div class="gst_charge" style="display: none;">
							<div class="form-group row cgst_charge">
								<label class="col-form-label col-lg-2">Cgst Stationary Charge<sup>*</sup></label>
								<div class="col-lg-4">
									<input type="text" name="cgst_stationary_charge" id="cgst_stationary_charge" class="form-control" readonly> </div>
								<label class="col-form-label col-lg-2">Sgst Stationary Charge<sup>*</sup></label>
								<div class="col-lg-4">
									<input type="text" name="sgst_stationary_charge" id="sgst_stationary_charge" class="form-control" readonly> </div>
							</div>
							<div class="form-group row igst_charge" style="display: none;">
								<label class="col-form-label col-lg-2">Igst Stationary Charge<sup>*</sup></label>
								<div class="col-lg-4">
									<input type="text" name="igst_stationary_charge" id="igst_stationary_charge" class="form-control" readonly> </div>
							</div>
						</div>
                <div class="plan-content-div">
                </div>

                <div class="text-right">
                  <input type="submit" name="submitform" value="Submit" class="btn btn-primary submit-investment">
                </div>  

                <div class="modal fade" id="modal-form" tabindex="-1" role="dialog" aria-labelledby="modal-form" aria-hidden="true">
                  <div class="modal-dialog modal- modal-dialog-centered modal-sm" role="document">
                    <div class="modal-content">
                      <div class="modal-body p-0">
                        <div class="card bg-white border-0 mb-0">
                          <div class="card-header bg-transparent pb-2ß">
                            <div class="text-dark text-center mt-2 mb-3">Enter member name<sup>*</sup></div>
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


 
            <div class="modal fade" id="modal-stationary-charge" tabindex="-1" role="dialog" aria-labelledby="modal-stationary-charge" aria-hidden="true"  data-backdrop="static" data-keyboard="false">
              <div class="modal-dialog modal- modal-dialog-centered modal-sm" role="document">
                <div class="modal-content">
                  <div class="modal-body p-0">
                    <div class="card bg-white border-0 mb-0">
                      <div class="card-body px-lg-5 py-lg-5">
                        <div class="text-dark text-center mt-2 mb-3">50/- Rupees stationary charges will be applicable on this investment plan registration.</div>
                        <div class="text-center">
                          <button type="button" class="btn btn-primary submitstationarycharge" form="modal-details">Confirm</button>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

          </div>
        </div>
        <!-- /basic layout -->
      </div>
    </div>

@stop

@section('script')
@include('templates.branch.investment_management.partials.script')
@stop
