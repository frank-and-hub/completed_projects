@extends('layouts/branch.dashboard')

@section('content')

<div class="loader" style="display: none;"></div>

  <div class="content">
    <div class="row">
      <div class="col-md-12">
        <!-- Basic layout-->
        <div class="card">
          <div class="card-header header-elements-inline">
            <h3 class="mb-0">Registration</h3>
                <div class="header-elements">
                  <div class="list-icons"></div>
              </div>
          </div>
          <div class="card-body">
            <form action="{{route('admin.investment.store')}}" method="post" id="register-plan" name="register-plan">
            @csrf
                <div class="form-group row">
                  <label class="col-form-label col-lg-2">Member Id<sup>*</sup></label>
                  <div class="col-lg-4">
                    <input type="text" name="memberid" id="memberid" class="form-control" placeholder="Member Id" required="" autocomplete="off">
                    <input type="hidden" name="memberAutoId" id="memberAutoId" class="form-control">
                  </div>

                  <label class="col-form-label col-lg-2">Branch Id<sup>*</sup></label>
                  <div class="col-lg-4">
                    <select name="branchid" id="branchid" class="form-control" title="Please select something!">
                      <option value="">Select Branch</option>
                        @foreach( App\Models\Branch::where('status', 1)->pluck('name','id') as $key => $val )
                            <option value="{{ $key }}" >{{ $val }}</option>
                        @endforeach

                    </select>
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

                <div class="alert alert-danger alert-block member-not-found" style="display: none;">  <strong>Member not found</strong> </div>

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
                <div class="form-group row select-plan" style="display: none;">
                  <label class="col-form-label col-lg-2">Investment Plan<sup>*</sup></label>
                  <div class="col-lg-4">
                    <select name="investmentplan" id="investmentplan" class="form-control" title="Please select something!">
                      <option value="">Select Plan</option>
                      {{--@foreach($plans as $plan)
                        <option data-val="{{ $plan->slug }}" value="{{ $plan->id }}">{{ $plan->name }}</option>
                      @endforeach--}}
                    </select>
                  </div>

                  <label class="col-form-label col-lg-2">Form Number<sup>*</sup></label>
                  <div class="col-lg-4">
                    <input type="text" name="form_number" id="form_number" class="form-control">
                  </div>
                </div> 

                <input class="form-control" type="hidden" name="plan_type" id="plan_type">

                <div class="plan-content-div">
                </div>

                <div class="text-right">
                  <input type="submit" name="submitform" value="Submit" class="btn btn-primary">
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


            <div class="modal fade" id="saving-account-modal-form" tabindex="-1" role="dialog" aria-labelledby="modal-form" aria-hidden="true">
              <div class="modal-dialog modal- modal-dialog-centered modal-sm" role="document">
                <div class="modal-content">
                  <div class="modal-body p-0">
                    <div class="card bg-white border-0 mb-0">
                      <div class="card-header bg-transparent pb-2ß">
                        <div class="text-dark text-center mt-2 mb-3">Open Saving Account</div>
                      </div>
                      <div class="card-body px-lg-5 py-lg-5">
                        <form action="{{route('investment.opensavingaccount')}}" method="post" id="saving-account-form" name="register-plan">
                          @csrf
                          <input type="hidden" name="saving_account_m_id" id="saving_account_m_id">
                          <input type="hidden" name="saving_account_m_name" id="saving_account_m_name">
                          <input type="hidden" name="nominee_form_class" id="nominee_form_class">
                          <input type="hidden" name="account_box_class" id="account_box_class">

                          <div class="form-group row">
                            <label class="col-form-label col-lg-2">Branch Id<sup>*</sup></label>
                            <div class="col-lg-12">
                              <select name="branchid" id="branchid" class="form-control" title="Please select something!">
                                <option value="">Select Branch</option>
                                  @foreach( App\Models\Branch::where('status', 1)->pluck('name','id') as $key => $val )
                                      <option value="{{ $key }}" >{{ $val }}</option>
                                  @endforeach
                                {{--@foreach($branches as $branch)
                                  <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach--}}
                              </select>
                            </div>
                          </div>

                          <div class="form-group row">
                            <label class="col-form-label col-lg-2">Amount</label>
                            <div class="col-lg-4">
                              <div class="rupee-img">
                              </div>
                              <input type="text" name="ssbamount" id="ssbamount" class="form-control rupee-txt" value="100" readonly="">
                            </div>

                            <label class="col-form-label col-lg-2">Form Number</label>
                            <div class="col-lg-4">
                              <input type="text" name="f_number" id="f_number" class="form-control">
                            </div>
                          </div>  

                          <h3 class="">First Nominee</h3>  
                          <div class="custom-control custom-checkbox mb-3 col-form-label">
                            <input type="checkbox" id="same_as_registered_ssb_nominee" name="same_as_registered_ssb_nominee" class="custom-control-input">
                            <label class="custom-control-label" for="same_as_registered_ssb_nominee">Yes</label>
                          </div>

                          <div class="form-group row">
                            <label class="col-form-label col-lg-2">Full Name<sup>*</sup></label>
                            <div class="col-lg-4">
                              <input type="text" name="ssb_fn_first_name" id="ssb_fn_first_name" class="form-control">
                            </div>
                            <label class="col-form-label col-lg-2">Relationship<sup>*</sup></label>
                            <div class="col-lg-4">
                              <select name="ssb_fn_relationship" id="ssb_fn_relationship" class="form-control" title="Please select something!">
                                <option value="">Select Relation</option>
                                {{--@foreach($relations as $relation)
                                  <option value="{{ $relation->id }}">{{ $relation->name }}</option>
                                @endforeach--}}
                              </select>
                            </div>
                          </div>                

                          <div class="form-group row">
                            <label class="col-form-label col-lg-2">Nominee (D.O.B.)<sup>*</sup></label>
                            <div class="col-lg-4">
                              <div class="input-group nominee-dob">
                                <span class="input-group-prepend">
                                  <span class="input-group-text"><i class="ni ni-calendar-grid-58"></i></span>
                                </span>
                                 <input type="text" name="ssb_fn_dob" id="ssb_fn_dob" class="fn_dateofbirth form-control" data-val="ssb_fn_age">
                              </div>
                            </div>
                            <label class="col-form-label col-lg-2">Age</label>
                            <div class="col-lg-4">
                              <input type="text" name="ssb_fn_age" id="ssb_fn_age" class="form-control">
                            </div>
                          </div>

                          <div class="form-group row">
                            <label class="col-form-label col-lg-2">Gender<sup>*</sup></label>
                            <div class="col-lg-4 error-msg">
                              <div class="row">
                                <div class="col-lg-4">
                                  <div class="custom-control custom-radio mb-3 CustomAddGender">
                                    <input type="radio" id="ssb_fn_gender_male" name="ssb_fn_gender" class="custom-control-input" value="1">
                                    <label class="custom-control-label" for="ssb_fn_gender_male">Male</label>
                                  </div>
                                </div>
                                <div class="col-lg-4">
                                  <div class="custom-control custom-radio mb-3 CustomAddGender">
                                    <input type="radio" id="ssb_fn_gender_female" name="ssb_fn_gender" class="custom-control-input" value="0">
                                    <label class="custom-control-label" for="ssb_fn_gender_female">Female</label>
                                  </div>
                                </div>
                              </div>
                            </div>
                            <label class="col-form-label col-lg-2">Percentage<sup>*</sup></label>
                            <div class="col-lg-4">
                              <input type="text" name="ssb_fn_percentage" id="ssb_fn_percentage" data-id="ssb_sn_percentage" class="form-control sa-nominee-percentage">
                              <label id="ssb-percentage-error" class="error"></label>
                            </div>
                          </div>

                          <h3 class="">Second Nominee</h3> 
                          <input type="button" name="second_nominee" data-val="sa_second_nominee_add" data-class="sa-second-nominee" id="second_nominee" value="Add Nominee" class="btn btn-primary valid second-nominee-input add-second-nominee sa-second-nominee-botton" aria-invalid="false">
                          <input type="hidden" name="sa_second_nominee_add" id="sa_second_nominee_add" value="0">

                          <div class="form-group row sa-second-nominee" style="display: none;">
                            <label class="col-form-label col-lg-2">Full Name<sup>*</sup></label>
                            <div class="col-lg-4">
                              <input type="text" name="ssb_sn_first_name" id="ssb_sn_first_name" class="form-control">
                            </div>
                            <label class="col-form-label col-lg-2">Relationship<sup>*</sup></label>
                            <div class="col-lg-4">
                              <select name="ssb_sn_relationship" id="ssb_sn_relationship" class="form-control" title="Please select something!">
                                <option value="">Select Relation</option>
                                {{--@foreach($relations as $relation)
                                  <option value="{{ $relation->id }}">{{ $relation->name }}</option>
                                @endforeach--}}
                              </select>
                            </div>
                            <!-- <label class="col-form-label col-lg-2">Last Name<sup>*</sup></label>
                            <div class="col-lg-4">
                              <input type="text" name="ssb_sn_second_name" id="ssb_sn_second_name" class="form-control">
                            </div> -->
                          </div> 

                          <div class="form-group row sa-second-nominee" style="display: none;">
                            <label class="col-form-label col-lg-2">Nominee (D.O.B.)<sup>*</sup></label>
                            <div class="col-lg-4">
                              <div class="input-group nominee-dob">
                              <span class="input-group-prepend">
                                <span class="input-group-text"><i class="ni ni-calendar-grid-58"></i></span>
                              </span>
                               <input type="text" name="ssb_sn_dob" id="ssb_sn_dob" class="sn_dateofbirth form-control" data-val="ssb_sn_age">
                             </div>
                            </div>
                            <label class="col-form-label col-lg-2">Age</label>
                            <div class="col-lg-4">
                              <input type="text" name="ssb_sn_age" id="ssb_sn_age" class="form-control" readonly="">
                            </div>
                          </div>

                          <div class="form-group row sa-second-nominee" style="display: none;">
                            <label class="col-form-label col-lg-2">Gender<sup>*</sup></label>
                            <div class="col-lg-4 error-msg">
                              <div class="row">
                                <div class="col-lg-4">
                                  <div class="custom-control custom-radio mb-3 CustomAddGender">
                                    <input type="radio" id="ssb_sn_gender_male" name="ssb_sn_gender" class="custom-control-input" value="1">
                                    <label class="custom-control-label" for="ssb_sn_gender_male">Male</label>
                                  </div>
                                </div>
                                <div class="col-lg-4">
                                  <div class="custom-control custom-radio mb-3 CustomAddGender">
                                    <input type="radio" id="ssb_sn_gender_female" name="ssb_sn_gender" class="custom-control-input" value="0">
                                    <label class="custom-control-label" for="ssb_sn_gender_female">Female</label>
                                  </div>
                                </div>
                              </div>
                            </div>
                            <label class="col-form-label col-lg-2">Percentage<sup>*</sup></label>
                            <div class="col-lg-4">
                              <input type="text" name="ssb_sn_percentage" id="ssb_sn_percentage" data-id="ssb_fn_percentage" class="form-control sa-nominee-percentage">
                            </div>
                          </div>

                          <div class="form-group row sa-second-nominee" style="display: none;">
                            
                          </div>
                          <div class="text-right">
                            <!-- <button type="button" class="btn btn-primary" form="modal-details">Submit</button> -->
                            <input type="submit" name="submitform" value="Submit" class="btn btn-primary">
                          </div>
                        </form>
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
  </div>
</div>
@stop

@section('script')
@include('templates.admin.investment_management.partials.script')
@stop
