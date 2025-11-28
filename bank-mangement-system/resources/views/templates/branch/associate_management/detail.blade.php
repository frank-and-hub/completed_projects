@extends('layouts/branch.dashboard')

@section('content')
<div class="loader" style="display: none;"></div>
<div class="container-fluid mt--6">
  <div class="content-wrapper">

    <div class="row">
      <div class="col-lg-12">
        <div class="card bg-white">
          <div class="card-body page-title"> 
            <h3 class="">Associate Detail</h3>
            <a href="{!! route('branch.associate_list') !!}" style="float:right" class="btn btn-secondary">Back</a>
          </div>
        </div>
      </div>
    </div>
    @if ( $memberDetail->is_block == 0 )
      <div class="row">
        <div class="col-lg-6">
          <div class="card bg-white" > 
            <div class="card-body">
              <h3 class="card-title mb-3">Associate Form Information</h3>
              <div class="row">
                  <label class=" col-lg-4">Form No</label><label class=" col-lg-1">:</label>
                  <div class="col-lg-7  "> {{ $memberDetail->associate_form_no }} </div>
              </div>
              <div class="row">
                <label class=" col-lg-4">Join Date </label><label class=" col-lg-1">:</label>
                <div class="col-lg-7  "> {{ date("d/m/Y", strtotime($memberDetail->associate_join_date)) }} </div>
              </div> 
              <div class="row">
                  <label class=" col-lg-4">Associate Id</label><label class=" col-lg-1">:</label>
                  <div class="col-lg-7  "> {{ $memberDetail->associate_no }} </div>
              </div>
              <div class="row">
                <label class=" col-lg-4">Member Id </label><label class=" col-lg-1">:</label>
                <div class="col-lg-7  "> <a href="{!! route('branch.memberDetail',['id'=>$memberDetail->id]) !!}"> {{ $memberDetail->member_id }} </a> </div>  
              </div>
              <div class="row">
                <label class=" col-lg-4">Carder </label><label class=" col-lg-1">:</label>
                <div class="col-lg-7  "> {{ getCarderName($memberDetail->current_carder_id) }} </div>  
              </div>

            </div>
          </div>
        </div>
       <!-- <div class="col-lg-6">
          <div class="card bg-white" > 
            <div class="card-body">
              <h3 class="card-title mb-3">Senior  Details</h3> 
              <div class="row">
                <label class=" col-lg-4">Senior Code </label><label class=" col-lg-1">:</label>
                <div class="col-lg-7  ">  @if($memberDetail->associate_senior_id==0)   Admin @else {{ $memberDetail->associate_senior_code }} @endif</div>  
              </div> 
              @if($memberDetail->associate_senior_id!=0)
              <div class="row">
                <label class=" col-lg-4">Name </label><label class=" col-lg-1">:</label>
                <div class="col-lg-7  ">{{ getSeniorData($memberDetail->associate_senior_id,'first_name') }}  {{ getSeniorData($memberDetail->associate_senior_id,'last_name') }}</div>  
              </div>

              <div class="row">
                <label class=" col-lg-4">Mobile No </label><label class=" col-lg-1">:</label>
                <div class="col-lg-7  ">{{ getSeniorData($memberDetail->associate_senior_id,'mobile_no') }}</div>  
              </div>

                @if ( $memberDetail->associate_senior_code != "9999999")
                  <div class="row">
                    <label class=" col-lg-4">Carder </label><label class=" col-lg-1">:</label>
                  <div class="col-lg-7  ">{{ getCarderName(getSeniorData($memberDetail->associate_senior_id,'current_carder_id')) }}</div>
                  </div>
                @endif

              @endif
            </div>
          </div>
        </div> -->
        <div class="col-lg-8">
            <div class="card bg-white" >
              <div class="card-body">
                <h3 class="card-title mb-3">Personal Information </h3>  

                <div class="row"> 
                    <label class=" col-lg-3">First Name </label><label class=" col-lg-1">:</label>
                    <div class="col-lg-8  "> {{ $memberDetail->first_name ? $memberDetail->first_name : 'N/A' }} </div>
                </div>
                <div class="row"> 
                    <label class=" col-lg-3">Last Name </label><label class=" col-lg-1">:</label>
                    <div class="col-lg-8  "> {{ $memberDetail->last_name ? $memberDetail->last_name : 'N/A'}} </div>
                </div> 

                <div class="row">
                    <label class=" col-lg-3">Email Id </label><label class=" col-lg-1">:</label>
                    <div class="col-lg-8  ">  {{ $memberDetail->email ? $memberDetail->email : 'N/A' }} </div>
                </div>
                <div class="row">
                    <label class=" col-lg-3">Mobile No</label><label class=" col-lg-1">:</label>
                    <div class="col-lg-8  "> {{ $memberDetail->mobile_no }} </div>
                </div>

                <div class="row">
                    <label class=" col-lg-3">Date of Birth </label><label class=" col-lg-1">:</label>
                    <div class="col-lg-8  "> {{ date("d/m/Y", strtotime($memberDetail->dob)) }} </div>
                </div>
                <div class="row">
                    <label class=" col-lg-3">Age</label><label class=" col-lg-1">:</label>
                    <div class="col-lg-8  "> {{ $memberDetail->age }} years</div>
                </div>
                <div class="row">
                    <label class=" col-lg-3">Gender </label><label class=" col-lg-1">:</label>
                    <div class="col-lg-8  "> @if($memberDetail->gender==1) Male @else Female @endif </div>
                </div>
                <div class="row">
                    <label class=" col-lg-3">Occupation</label><label class=" col-lg-1">:</label>
                    <div class="col-lg-8  "> {{ getOccupationName($memberDetail->occupation_id) }} </div>
                </div>

                <div class="row">
                    <label class=" col-lg-3">Annual Income </label><label class=" col-lg-1">:</label>
                    <div class="col-lg-8  "> {{ number_format($memberDetail->annual_income, 2, '.', ',') }} <img src="{{url('/')}}/asset/images/rs.png" width="9">  </div>
                </div>
                <div class="row">
                    <label class=" col-lg-3">Status </label><label class=" col-lg-1">:</label>
                    <div class="col-lg-8  "> @if($memberDetail->associate_status==1) Active @else  Inactive @endif  </div>
                </div> 
                <div class="row">
                    <label class=" col-lg-3">Address </label><label class=" col-lg-1" >:</label>
                    <div class="col-lg-8 "> {{ $memberDetail->address }} </div>
                </div>

                <div class="row">
                    <label class=" col-lg-3">State </label><label class=" col-lg-1">:</label>
                    <div class="col-lg-8  "> {{ getStateName($memberDetail->state_id) }}   </div>
                </div>
                <div class="row">
                    <label class=" col-lg-3">District </label><label class=" col-lg-1">:</label>
                    <div class="col-lg-8  "> {{ getDistrictName($memberDetail->district_id) }} </div>
                </div> 

                <div class="row">
                    <label class=" col-lg-3">City </label><label class=" col-lg-1">:</label>
                    <div class="col-lg-8  "> {{ getCityName($memberDetail->city_id) }} </div>
                </div>
                <div class="row">
                    <label class=" col-lg-3">Village Name </label><label class=" col-lg-1">:</label>
                    <div class="col-lg-8  ">{{ $memberDetail->village }}</div>
                </div>

                <div class="row">
                    <label class=" col-lg-3">Pin Code </label><label class=" col-lg-1">:</label>
                    <div class="col-lg-8  "> {{ $memberDetail->pin_code }} </div> 
                </div>

            </div>
          </div>
        </div>
        <div class="col-lg-4">
          <div class="card bg-white" > 
            <div class="card-body">
              <h3 class="card-title mb-3">Profile Image </h3>
              <div class="row">
                <div class="  text-center">
                    <div class="col-lg-12  ">
                      <div class="profile_image_div">
                      <label> <i class="fas fa-camera"></i>
                        <form enctype="multipart/form-data" id="upload_form" method="POST" action="{{ route('member.image') }}">
                          @csrf
                          <input type="hidden" name="member-id" id="member-id" value="{{ $memberDetail->id }}">
                          <input type="file" name="photo" class="profile_image" id="photo">
                        </form>
                      </label>
                      @if($memberDetail->photo=='')
                          <img class="rounded-circle w-100" alt="Image placeholder" src="{{url('/')}}/asset/images/user.png">
                      @else
                          <!-- <img class="rounded-circle w-100" alt="Image placeholder" src="{{url('/')}}/asset/profile/member_avatar/{{ $memberDetail->photo }}"> -->
                          <img class="rounded-circle w-100" alt="Image placeholder" src="{{ImageUpload::generatePreSignedUrl('profile/member_avatar/' . $memberDetail->photo) }}">
                      @endif
                      </div>
                        @if($memberDetail->photo=='')
                          <label class="profile_blocked">Note<sup class="required">*</sup>
                            Upload picture otherwise the account will be blocked after 30 days
                          </label>
                      @endif
                    </div>
                  </div>
              </div>
            </div>
          </div>
          <div class="card bg-white" > 
            <div class="card-body">
              <h3 class="card-title mb-3">Signature </h3>
              <div class="row">
                <div class="  text-center">
                    <div class="col-lg-12 ">
                      <div class="signature_image_div">
                    
                      @if($memberDetail->signature=='')
                          <img class="" alt="Image placeholder" style="width:100%" src="{{url('/')}}/asset/images/signature-logo-design.png">
                      @else
                          <!-- <img class="w-100" alt="Image placeholder" src="{{url('/')}}/asset/profile/member_signature/{{ $memberDetail->signature }}"> -->
                          <img class="w-100" alt="Image placeholder" src="{{ImageUpload::generatePreSignedUrl('profile/member_signature/' . $memberDetail->signature) }}">
                      @endif
						  
					  <label>
                        <form enctype="multipart/form-data" id="signature_form" method="POST" action="{{ route('member.image') }}">
                          @csrf
						<div class="custom-file  error-msg">
                          <input type="hidden" name="member-id" id="member-id" value="{{ $memberDetail->id }}">
                          <input type="file" name="signature" class="signature_image custom-file-input" id="signature">
						  <label class="custom-file-label" for="signature">Select document</label>
						</div>
                        </form>
                      </label>
			     </div>
                      @if($memberDetail->signature=='')
                        <label class="profile_blocked">Note<sup class="required">*</sup>
                          Upload signature otherwise the account will be blocked after 30 days
                        </label>
                      @endif
                    </div>
                  </div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-lg-12">
          <div class="card bg-white" > 
            <div class="card-body">
              <h3 class="card-title mb-3">ID Proof</h3>
              <div class="row">
                @if($idProofDetail)
                <div class="col-lg-6">
                  <h5 class="card-title mb-3">First ID Proof </h5>
                  <div class="row">
                    <label class=" col-lg-3">ID Proof Type </label><label class=" col-lg-1">:</label>
                    <div class="col-lg-8  "> {{ getIdProofName($idProofDetail->first_id_type_id)}} </div>  
                  </div>  
                  <div class="row">
                    <label class=" col-lg-3">ID No </label><label class=" col-lg-1">:</label>
                    <div class="col-lg-8  ">{{ $idProofDetail->first_id_no }}</div>  
                  </div>
                  <div class="row">
                    <label class=" col-lg-3">Address </label><label class=" col-lg-1">:</label>
                    <div class="col-lg-8  ">{{ $idProofDetail->first_address }}</div>  
                  </div> 
                </div>
                <div class="col-lg-6">
                  <h5 class="card-title mb-3">Second ID Proof</h5> 
                  <div class="row">
                    <label class=" col-lg-3">ID Proof Type </label><label class=" col-lg-1">:</label>
                    <div class="col-lg-8  ">{{ getIdProofName($idProofDetail->second_id_type_id)}}</div>  
                  </div>
                  <div class="row">
                    <label class=" col-lg-3">ID No </label><label class=" col-lg-1">:</label>
                    <div class="col-lg-8  ">{{ $idProofDetail->second_id_no }}</div>  
                  </div> 
                  <div class="row">
                    <label class=" col-lg-3">Address </label><label class=" col-lg-1">:</label>
                    <div class="col-lg-8  ">{{ $idProofDetail->second_address }}</div>  
                  </div> 
                </div>
                @else
                <div class="col-lg-12">
                    <label class=" col-lg-12">No record found!. </label> 
                </div>
                @endif
              </div>
            </div>
          </div>          
        </div>

        <div class="col-lg-12">
          <div class="card bg-white" > 
            <div class="card-body">
              <h3 class="card-title mb-3">Guarantor Details </h3>
              <div class="row">
                @if($guarantorDetail)
                <div class="col-lg-6">
                  <h5 class="card-title mb-3">1<sup>st</sup>Guarantor Detail </h5>
                  
                  <div class="row">
                    <label class=" col-lg-3">Full Name</label><label class=" col-lg-1">:</label>
                    <div class="col-lg-8  "> {{ $guarantorDetail->first_name}} </div>  
                  </div>   
                  <div class="row">
                    <label class=" col-lg-3">Mobile No</label><label class=" col-lg-1">:</label>
                    <div class="col-lg-8  ">{{ $guarantorDetail->first_mobile_no  }}</div>  
                  </div>
                  <div class="row">
                    <label class=" col-lg-3">Address </label><label class=" col-lg-1">:</label>
                    <div class="col-lg-8  ">{{ $guarantorDetail->first_address}}</div>  
                  </div> 
                  
                </div>
                @if ($guarantorDetail->second_name)
                  <div class="col-lg-6">
                  <h5 class="card-title mb-3">2<sup>nd</sup>Guarantor Detail </h5> 
                  <div class="row">
                    <label class=" col-lg-3">Full Name</label><label class=" col-lg-1">:</label>
                    <div class="col-lg-8  "> {{ $guarantorDetail->second_name}} </div>  
                  </div>  
                  <div class="row">
                    <label class=" col-lg-3">Mobile No</label><label class=" col-lg-1">:</label>
                    <div class="col-lg-8  ">{{ $guarantorDetail->second_mobile_no  }}</div>  
                  </div>
                  <div class="row">
                    <label class=" col-lg-3">Address </label><label class=" col-lg-1">:</label>
                    <div class="col-lg-8  ">{{ $guarantorDetail->second_address}}</div>  
                  </div> 
                </div>
                @endif
                @else
                <div class="col-lg-12">
                    <label class=" col-lg-12">No record found!. </label> 
                </div>
                @endif
              </div>
            </div>
          </div>          
        </div>
        @if( count( $dependentDetail ) > 0 )
        <div class="col-lg-12">
          <div class="card bg-white" > 
            <div class="card-body">
              <h3 class="card-title mb-3">Details of Associate's  dependents </h3>
              
                @if($dependentDetail)
                  @foreach($dependentDetail as $val)
                    <div class="row">
                      <div class="col-lg-6">
                        <div class="  row">
                        <label class=" col-lg-4">Full Name</label><label class=" col-lg-1">:</label>
                        <div class="col-lg-7  "> {{ $val->name }} </div>
                      </div>
                      </div>
                      <div class="col-lg-6">
                        <div class="  row">
                        <label class=" col-lg-4">Dependent Type </label><label class=" col-lg-1">:</label>
                        <div class="col-lg-7  "> @if($val->dependent_type==1) Fully @else Partially  @endif </div>
                      </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-lg-6">
                        <div class="  row">
                        <label class=" col-lg-4">Gender</label><label class=" col-lg-1">:</label>
                        <div class="col-lg-7  ">@if($val->gender==1) Male @else Female @endif</div>
                      </div>
                      </div>
                      <div class="col-lg-6">
                        <div class="  row">
                        <label class=" col-lg-4">Age  </label><label class=" col-lg-1">:</label>
                        <div class="col-lg-7  "> {{ $val->age }} year </div>
                      </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-lg-6">
                        <div class="  row">
                        <label class=" col-lg-4">Relation  </label><label class=" col-lg-1">:</label>
                        <div class="col-lg-7  "> @if($val->relation>0) {{ getRelationsName($val->relation) }} @endif </div>
                      </div>
                      </div>
                      <div class="col-lg-6">
                        <div class="  row">
                        <label class=" col-lg-4">Marital status </label><label class=" col-lg-1">:</label>
                        <div class="col-lg-7  ">@if($val->marital_status==1) Married @else Un Married @endif</div>
                      </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-lg-6">
                        <div class="  row">
                        <label class=" col-lg-4">Living with Associate </label><label class=" col-lg-1">:</label>
                        <div class="col-lg-7  "> @if($val->living_with_associate==1) Yes @else No @endif </div>
                      </div>
                      </div>
                      <div class="col-lg-6">  
                        <div class="  row">
                        <label class=" col-lg-4">Per month income </label><label class=" col-lg-1">:</label>
                        <div class="col-lg-7  ">{{ number_format($val->monthly_income, 2, '.', ',') }}<img src="{{url('/')}}/asset/images/rs.png" width="9"></div>
                      </div>
                      </div>
                    </div> 

                  @endforeach 
                @else
                <div class="row">
                  <div class="col-lg-12">
                      <label class=" col-lg-12">No record found!. </label> 
                  </div>
                </div>
                @endif
            </div>
          </div>          
        </div>
        @endif

        <div class="col-lg-12">
          <div class="card bg-white" >            
            <div class="card-body">
              <div class="text-center">
              {{--<a href="{!! route('branch.member_list') !!}" class="btn btn-secondary">Back</a>--}}
              @if($recipt>0)
              <a href="{!! route('branch.associate_receiept',['id'=>$recipt]) !!}" class="btn btn-primary">Receipt</a>
              @endif
            </div>
            </div>
          </div>
        </div>

      </div>
    @else
      <div class="row">
        <div class="col-lg-6">
          <div class="card bg-white" >
            <div class="card-body">
              <h3 class="card-title mb-3">Profile Image </h3>
              <div class="row">
                <div class="col-lg-12">
                  <div class="profile_image_div">
                    <label> <i class="fas fa-camera"></i>
                      <form enctype="multipart/form-data" id="upload_form" method="POST" action="{{ route('member.image') }}">
                        @csrf
                        <input type="hidden" name="member-id" id="member-id" value="{{ $memberDetail->id }}">
                        <input type="file" name="photo" class="profile_image" id="photo">
                      </form>
                    </label>

                    @if($memberDetail->photo=='')
                      <img class="rounded-circle " alt="Image placeholder" src="{{url('/')}}/asset/images/user.png">

                    @else
                      <!-- <img class="rounded-circle w-100" alt="Image placeholder" src="{{url('/')}}/asset/profile/member_avatar/{{ $memberDetail->photo }}"> -->
                      <img class="rounded-circle w-100" alt="Image placeholder" src="{{ImageUpload::generatePreSignedUrl('profile/member_avatar/' .  $memberDetail->photo)}}">
                  @endif
                  <!--  <embed src="{{url('/')}}/asset/profile/member_avatar/6143XXXXXXXXX553319-05-2019.pdf" type="application/pdf" width="100%" height="100%"> -->
                  </div>
                  @if($memberDetail->photo=='')
                    <label class="profile_blocked">Note<sup class="required">*</sup>
                      Upload picture otherwise the account will be blocked after 30 days
                    </label>
                  @endif
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-lg-6">
          <div class="card bg-white" >
            <div class="card-body">
              <h3 class="card-title mb-3">Signature</h3>
              <div class="row">
                <div class="col-lg-12 ">
                  <div class="signature_image_div">
                    <label> <i class="fas fa-camera"></i>
                      <form enctype="multipart/form-data" id="signature_form" method="POST" action="{{ route('member.image') }}">
                        @csrf
                        <input type="hidden" name="member-id" id="member-id" value="{{ $memberDetail->id }}">
                        <input type="file" name="signature" class="signature_image" id="signature">
                      </form>
                    </label>

                    @if($memberDetail->signature=='')
                      <img class="rounded-circle " alt="Image placeholder" src="{{url('/')}}/asset/images/signature-logo-design.png">

                    @else
                      <!-- <img class="rounded-circle w-100" alt="Image placeholder" src="{{url('/')}}/asset/profile/member_signature/{{ $memberDetail->signature }}"> -->
                      <img class="rounded-circle w-100" alt="Image placeholder" src="{{ImageUpload::generatePreSignedUrl('profile/member_signature/' .  $memberDetail->signature)}}">
                    @endif
                  </div>
                  @if($memberDetail->signature=='')
                    <label class="profile_blocked">Note<sup class="required">*</sup>
                      Upload signature otherwise the account will be blocked after 30 days
                    </label>
                  @endif
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    @endif

  </div>
</div>    
@stop


@section('script')

@include('templates.branch.member_management.partials.script')
@stop