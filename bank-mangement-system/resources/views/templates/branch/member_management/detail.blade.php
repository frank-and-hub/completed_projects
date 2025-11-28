@extends('layouts/branch.dashboard')

@section('content')
<div class="loader" style="display: none;"></div>
<div class="container-fluid mt--6">
  <div class="content-wrapper">
    <div class="row">
      <div class="col-lg-12">
        <div class="card bg-white">
          <div class="card-body page-title ">
            <h3 class=""> @if($_GET['type']==1) Customer @else Member @endif Detail </h3>
            <a href="{!! route('branch.member_list') !!}" style="float:right" class="btn btn-secondary">Back</a>
          </div>
        </div>
      </div>
    </div>
    @if ( $memberDetail->is_block == 0 )
    <div class="d-flex mb-4">
      <!--  
        <ul class="nav ">
            <li class="nav-item">
            <a href="{{URL::to("admin/member-edit/".$memberDetail->id."")}}" class="btn btn-primary"><span>Edit</span></a>
            
            </li>
        </ul>
        <ul class="nav ml-4 ">
              <li class="nav-item">
              <a href="#" class="btn btn-primary"><span>TDS Certificate</span></a>
              
              </li>
          </ul> 
        <ul class="nav">
            <li class="nav-item dropdown">
              @if($idProofDetail->first_id_type_id==5)
              <a class="btn btn-primary" href="{{URL::to("branch/form_g/".$memberDetail->id."")}}">Update From 15G/15H</a>
              @endif
            </li>
        </ul> 
      -->
    </div>
    <div class="row">
      <div class="col-lg-6">
        <div class="card bg-white">
          <div class="card-body">
            <h3 class="card-title mb-3">@if($_GET['type']==1) Customer @else Member @endif Form Information </h3>
            <div class="row">
              <label class=" col-lg-4">Form No</label><label class=" col-lg-1">:</label>
              <div class="col-lg-7  "> {{ $memberDetail->form_no }} </div>
            </div>
            <div class="row">
              <label class=" col-lg-4">Join Date </label><label class=" col-lg-1">:</label>
              <div class="col-lg-7  "> {{ date("d/m/Y", strtotime($memberDetail->re_date)) }} </div>
            </div>
            <div class="row">
              <label class=" col-lg-4">Customer Id </label><label class=" col-lg-1">:</label>
              <div class="col-lg-7  "> {{ $memberDetail->member_id }} </div>
            </div>
            @if($_GET['type']==0)
            <div class="row">
              <label class=" col-lg-4">Member Id </label><label class=" col-lg-1">:</label>
              <div class="col-lg-7  "> {{ $membercompany->member_id }} </div>
            </div>
            <!-- <div class="row">
                <label class=" col-lg-4">Branch MI </label><label class=" col-lg-1">:</label>
                <div class="col-lg-7  "> {{ $membercompany->branch_mi }} </div>  
              </div> -->
            @endif
            <div class="row">
                <label class=" col-lg-4">Is Employee </label><label class=" col-lg-1">:</label>
                <div class="col-lg-7  "> {{ $memberDetail->is_employee == 1 ? 'Yes' : 'No' }} </div>  
            </div>
            @if($memberDetail->is_employee == '1')
            <div class="row">
                <label class=" col-lg-4">Employee Code </label><label class=" col-lg-1">:</label>
                <div class="col-lg-7  "> {{ $memberDetail->employee_code }} </div>  
            </div>                    
            @endif
          </div>
        </div>
      </div>
      <div class="col-lg-6">
        <div class="card bg-white">
          <div class="card-body">
            <h3 class="card-title mb-3">Associate Details</h3>
            @if($_GET['type']==1)
              <div class="row">
                <label class=" col-lg-4">Associate Code </label><label class=" col-lg-1">:</label>
                <div class="col-lg-7  "> @if($memberDetail->associate_id==0) Member Associate With Admin @else {{$memberDetail['children']->associate_no }} @endif</div>
              </div>
              @if($memberDetail->associate_id!=0)
              <div class="row">
                <label class=" col-lg-4">Associate Name </label><label class=" col-lg-1">:</label>
                <div class="col-lg-7  "> {{ $memberDetail['children']->first_name }} {{$memberDetail['children']->last_name }} </div>
              </div>

              <div class="row">
                <label class=" col-lg-4">Customer ID </label><label class=" col-lg-1">:</label>
                <div class="col-lg-7  "> {{ $memberDetail['children']->member_id}} </div>
              </div>
              @endif
            @else
              <div class="row">
                <label class=" col-lg-4">Associate Code </label><label class=" col-lg-1">:</label>
                <div class="col-lg-7  "> @if($membercompany->associate_id==0) Member Associate With Admin @else {{$membercompany['memberAssociate']->associate_no }} @endif</div>
              </div>
              @if($membercompany->associate_id!=0)
              <div class="row">
                <label class=" col-lg-4">Associate Name </label><label class=" col-lg-1">:</label>
                <div class="col-lg-7  "> {{ $membercompany['memberAssociate']->first_name }} {{$membercompany['memberAssociate']->last_name}} </div>
              </div>
              <div class="row">
                <label class=" col-lg-4">Customer ID </label><label class=" col-lg-1">:</label>
                <div class="col-lg-7  "> {{ $membercompany['memberAssociate']->member_id}} </div>
              </div>
              @endif
            @endif
          </div>
        </div>
      </div>

      <div class="col-lg-12">
        <div class="card bg-white">
          <div class="card-body">
            <h3 class="card-title mb-3">Personal Information </h3>
            <div class="row">
              <div class="col-lg-6">
                <div class="  row">
                  <label class=" col-lg-4">First Name </label><label class=" col-lg-1">:</label>
                  <div class="col-lg-7  "> {{ $memberDetail->first_name }} </div>
                </div>
              </div>
              <div class="col-lg-6">
                <div class="  row">
                  <label class=" col-lg-4">Last Name </label><label class=" col-lg-1">:</label>
                  <div class="col-lg-7  "> {{ $memberDetail->last_name }} </div>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-lg-6">
                <div class="  row">
                  <label class=" col-lg-4">Email Id </label><label class=" col-lg-1">:</label>
                  <div class="col-lg-7  "> {{ $memberDetail->email }} </div>
                </div>
              </div>
              <div class="col-lg-6">
                <div class="  row">
                  <label class=" col-lg-4">Mobile No</label><label class=" col-lg-1">:</label>
                  <div class="col-lg-7  "> {{ $memberDetail->mobile_no }} </div>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-lg-6">
                <div class="  row">
                  <label class=" col-lg-4">Date of Birth </label><label class=" col-lg-1">:</label>
                  <div class="col-lg-7  "> {{ date("d/m/Y", strtotime($memberDetail->dob)) }} </div>
                </div>
              </div>
              <div class="col-lg-6">
                <div class="  row">
                  <label class=" col-lg-4">Age</label><label class=" col-lg-1">:</label>
                  <div class="col-lg-7  "> {{ $memberDetail->age }} </div>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-lg-6">
                <div class="  row">
                  <label class=" col-lg-4">Gender </label><label class=" col-lg-1">:</label>
                  <div class="col-lg-7  "> @if($memberDetail->gender==1) Male @else Female @endif </div>
                </div>
              </div>
              <div class="col-lg-6">
                <div class="  row">
                  <label class=" col-lg-4">Occupation</label><label class=" col-lg-1">:</label>
                  <div class="col-lg-7  "> {{ getOccupationName($memberDetail->occupation_id) }} </div>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-lg-6">
                <div class="  row">
                  <label class=" col-lg-4">Annual Income </label><label class=" col-lg-1">:</label>
                  <div class="col-lg-7  "> {{ number_format($memberDetail->annual_income, 2, '.', ',') }} <img src="{{url('/')}}/asset/images/rs.png" width="9"> </div>
                </div>
              </div>
              <div class="col-lg-6">
                <div class="  row">
                  <label class=" col-lg-4">Mother Name</label><label class=" col-lg-1">:</label>
                  <div class="col-lg-7  "> {{ $memberDetail->mother_name }} </div>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-lg-6">
                <div class="  row">
                  <label class=" col-lg-4">Father/ Husband's Name </label><label class=" col-lg-1">:</label>
                  <div class="col-lg-7  "> {{ $memberDetail->father_husband }}</div>
                </div>
              </div>
              <div class="col-lg-6">
                <div class="  row">
                  <label class=" col-lg-4">Marital status</label><label class=" col-lg-1">:</label>
                  <div class="col-lg-7  "> @if($memberDetail->marital_status==1) Married @else Un Married @endif </div>
                </div>
              </div>
            </div>
            <div class="row">
              @if($memberDetail->anniversary_date)
              <div class="col-lg-6">
                <div class="  row">
                  <label class=" col-lg-4">Anniversary Date </label><label class=" col-lg-1">:</label>
                  <div class="col-lg-7  "> {{ date("d/m/Y", strtotime($memberDetail->anniversary_date)) }} </div>
                </div>
              </div>
              @endif
              <div class="col-lg-6">
                <div class="  row">
                  <label class=" col-lg-4">Religion</label><label class=" col-lg-1">:</label>
                  <div class="col-lg-7  "> @if($memberDetail->religion_id>0) {{
                    getReligionName($memberDetail->religion_id) }} @endif </div>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-lg-6">
                <div class="  row">
                  <label class=" col-lg-4"> Categories </label><label class=" col-lg-1">:</label>
                  <div class="col-lg-7  "> @if($memberDetail->special_category_id>0) {{
                    getSpecialCategoryName($memberDetail->special_category_id) }} @else General Category @endif
                  </div>
                </div>
              </div>
              <div class="col-lg-6">
                <div class="  row">
                  <label class=" col-lg-4">Status </label><label class=" col-lg-1">:</label>
                  <div class="col-lg-7  "> @if($memberDetail->status==1) Active @else Inactive @endif </div>
                </div>
              </div>
            </div>
            <h5 class="card-title mb-3">Residence Address</h5>
            <div class="row">
              <div class="col-lg-6">
                <div class="  row">
                  <label class=" col-lg-4">Address </label><label class=" col-lg-1">:</label>
                  <div class="col-lg-7  "> {{ $memberDetail->address }} </div>
                </div>
              </div>
              <div class="col-lg-6">
                <div class="  row">
                  <label class=" col-lg-4">Pin Code </label><label class=" col-lg-1">:</label>
                  <div class="col-lg-7  "> {{ $memberDetail->pin_code }} </div>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-lg-6">
                <div class="  row">
                  <label class=" col-lg-4">State </label><label class=" col-lg-1">:</label>
                  <div class="col-lg-7  "> {{ getStateName($memberDetail->state_id) }} </div>
                </div>
              </div>
              <div class="col-lg-6">
                <div class="  row">
                  <label class=" col-lg-4">District </label><label class=" col-lg-1">:</label>
                  <div class="col-lg-7  "> {{ getDistrictName($memberDetail->district_id) }} </div>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-lg-6">
                <div class="  row">
                  <label class=" col-lg-4">City </label><label class=" col-lg-1">:</label>
                  <div class="col-lg-7  "> {{ getCityName($memberDetail->city_id) }} </div>
                </div>
              </div>
              <div class="col-lg-6">
                <div class="  row">
                  <label class=" col-lg-4">Village Name </label><label class=" col-lg-1">:</label>
                  <div class="col-lg-7  ">{{ $memberDetail->village }}</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>


      <div class="col-lg-8">
        <div class="card bg-white">
          <div class="card-body">
            <h3 class="card-title mb-3">ID Proof</h3>
            <h5 class="card-title mb-3">First ID Proof </h5>
            <div class="row">
              <label class=" col-lg-4">ID Proof Document Type </label><label class=" col-lg-1">:</label>
              <div class="col-lg-7  "> {{ getIdProofName($idProofDetail->first_id_type_id)}} </div>
            </div>
            <div class="row">
              <label class=" col-lg-4">ID No </label><label class=" col-lg-1">:</label>
              <div class="col-lg-7  ">{{ $idProofDetail->first_id_no }}</div>
            </div>
            <div class="row">
              <label class=" col-lg-4">Address </label><label class=" col-lg-1">:</label>
              <div class="col-lg-7  ">{{ $idProofDetail->first_address }}</div>
            </div>
            <h5 class="card-title mb-3">Second ID Proof (Address Proof)</h5>
            <div class="row">
              <label class=" col-lg-4">ID Proof Document Type </label><label class=" col-lg-1">:</label>
              <div class="col-lg-7  ">{{ getIdProofName($idProofDetail->second_id_type_id)}}</div>
            </div>
            <div class="row">
              <label class=" col-lg-4">ID No </label><label class=" col-lg-1">:</label>
              <div class="col-lg-7  ">{{ $idProofDetail->second_id_no }}</div>
            </div>
            <div class="row">
              <label class=" col-lg-4">Address </label><label class=" col-lg-1">:</label>
              <div class="col-lg-7  ">{{ $idProofDetail->second_address }}</div>
            </div>

          </div>
        </div>

        <div class="card bg-white">
          <div class="card-body">
            <h3 class="card-title mb-3">Nominee Information</h3>
            <div class="row">
              <label class=" col-lg-4">Full Name </label><label class=" col-lg-1">:</label>
              <div class="col-lg-7  "> {{ $nomineeDetail->name }} </div>
            </div>
            <div class="row">
              <label class=" col-lg-4">Relationship</label><label class=" col-lg-1">:</label>
              <div class="col-lg-7  "> @if($nomineeDetail->relation>0) {{ getRelationsName($nomineeDetail->relation) }} @endif </div>
            </div>
            <div class="row">
              <label class=" col-lg-4">Gender</label><label class=" col-lg-1">:</label>
              <div class="col-lg-7  "> @if($nomineeDetail->gender ==1) Male @else Female @endif </div>
            </div>
            <div class="row">
              <label class=" col-lg-4">Date of Birth</label><label class=" col-lg-1">:</label>
              <div class="col-lg-7  "> @if($nomineeDetail->dob) {{ date("d/m/Y", strtotime($nomineeDetail->dob)) }}@endif </div>
            </div>
            <div class="row">
              <label class=" col-lg-4">Age</label><label class=" col-lg-1">:</label>
              <div class="col-lg-7  "> @if($nomineeDetail->dob) {{ $nomineeDetail->age }} @endif </div>
            </div>
            <div class="row">
              <label class=" col-lg-4">Mobile No</label><label class=" col-lg-1">:</label>
              <div class="col-lg-7  "> {{ $nomineeDetail->mobile_no }} </div>
            </div>
            <div class="row">
              <label class=" col-lg-4">Is Minor</label><label class=" col-lg-1">:</label>
              <div class="col-lg-7  "> @if($nomineeDetail->is_minor ==1) Yes @else No @endif</div>
            </div>
            @if($nomineeDetail->is_minor ==1)
            <div class="row">
              <label class=" col-lg-4">Parent Name</label><label class=" col-lg-1">:</label>
              <div class="col-lg-7  "> {{ $nomineeDetail->parent_name }} </div>
            </div>
            <div class="row">
              <label class=" col-lg-4">Parent Mobile No</label><label class=" col-lg-1">:</label>
              <div class="col-lg-7  "> {{ $nomineeDetail->parent_no }} </div>
            </div>
            @endif
          </div>
        </div>

      </div>
      <div class="col-lg-4">
        <div class="card bg-white">
          <div class="card-body">
            <h3 class="card-title mb-3">Profile Image </h3>
            <div class="row">
              <div class="col-lg-12">
                <div class="profile_image_div">
                  @if($memberDetail->photo == '')
                  <label> <i class="fas fa-camera"></i>
                    <form enctype="multipart/form-data" id="upload_form" method="POST" action="{{ route('member.image') }}">
                      @csrf
                      <input type="hidden" name="member-id" id="member-id" value="{{ $memberDetail->id }}">
                      <input type="file" name="photo" class="profile_image" id="photo">
                    </form>
                  </label>
                  <img class="rounded-circle " alt="Image placeholder" src="{{url('/')}}/asset/images/user.png">
                  @else
                  <?php
                      $foldermember_avatarName = 'profile/member_avatar/' . $memberDetail->photo;
                      $url = ImageUpload::generatePreSignedUrl($foldermember_avatarName);
                  ?>
                  <img class="rounded-circle w-100" alt="Image placeholder" src="{{$url }}">
                  @endif
                </div>
                @if($memberDetail->photo == '')
                <label class="profile_blocked">Note<sup class="required">*</sup>Upload picture otherwise the account will be blocked after 30 days </label>
                @endif
              </div>
            </div>
          </div>
        </div>
        <div class="card bg-white">
          <div class="card-body">
            <h3 class="card-title mb-3">Signature</h3>
            <div class="row">
              <div class="col-lg-12 ">
                <div class="signature_image_div">
                  @if($memberDetail->signature=='')
                  <img class="" alt="Image placeholder" src="{{url('/')}}/asset/images/signature-logo-design.png">
                  <label>
                    <form enctype="multipart/form-data" id="signature_form" method="POST" action="{{ route('member.image') }}">
                      @csrf
                      <div class="custom-file error-msg">
                        <input type="hidden" name="member-id" id="member-id" value="{{ $memberDetail->id }}">
                        <input type="file" name="signature" class="signature_image custom-file-input" id="signature">
                        <label class="custom-file-label" for="signature">Select document</label>
                      </div>
                    </form>
                  </label>
                  @else
                  <?php
                      $foldermember_signatureName = 'profile/member_signature/' . $memberDetail->signature;
                      $member_signatureurl = ImageUpload::generatePreSignedUrl($foldermember_signatureName);
                  ?>
                  <img class="rounded-circle w-100" alt="Image placeholder" src="{{$member_signatureurl}}">
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
      <div class="col-lg-12">
        <div class="card bg-white">
          <div class="card-body">
            <h3 class="card-title mb-3">Bank Information</h3>
            @if($bankDetail)
            <div class="row">
              <div class="col-lg-6">
                <div class="  row">
                  <label class=" col-lg-4">Bank Name </label><label class=" col-lg-1">:</label>
                  <div class="col-lg-7  "> {{ $bankDetail->bank_name }} </div>
                </div>
              </div>
              <div class="col-lg-6">
                <div class="  row">
                  <label class=" col-lg-4">Branch Name</label><label class=" col-lg-1">:</label>
                  <div class="col-lg-7  "> {{ $bankDetail->branch_name }} </div>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-lg-6">
                <div class="  row">
                  <label class=" col-lg-4">Bank A/C No </label><label class=" col-lg-1">:</label>
                  <div class="col-lg-7  "> {{ $bankDetail->account_no }}</div>
                </div>
              </div>
              <div class="col-lg-6">
                <div class="  row">
                  <label class=" col-lg-4">IFSC Code</label><label class=" col-lg-1">:</label>
                  <div class="col-lg-7  ">{{ $bankDetail->ifsc_code }}</div>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-lg-12">
                <div class="  row">
                  <label class=" col-lg-2">Bank Address </label><label class=" col-lg-1"
                    style="max-width: 4.2%">:</label>
                  <div class="col-lg-9  "> {{ $bankDetail->address }} </div>
                </div>
              </div>
            </div>
            @else
            <div class="row">
              <div class="col-lg-12"> Bank detail not found! </div>
            </div>
            @endif

          </div>
        </div>
      </div>
    </div>
  </div>
  @else
  <div class="row">
    <div class="col-lg-6">
      <div class="card bg-white">
        <div class="card-body">
          <h3 class="card-title mb-3">Profile Image </h3>
          <div class="row">
            <div class="col-lg-12">
              <div class="profile_image_div">
                @if($memberDetail->photo=='')
                <label> <i class="fas fa-camera"></i>
                  <form enctype="multipart/form-data" id="upload_form" method="POST" action="{{ route('member.image') }}">
                    @csrf
                    <input type="hidden" name="member-id" id="member-id" value="{{ $memberDetail->id }}">
                    <input type="file" name="photo" class="profile_image" id="photo">
                  </form>
                </label>
                <img class="rounded-circle " alt="Image placeholder" src="{{url('/')}}/asset/images/user.png">
                @else
                <?php
                  $member_avatarfolderName = 'profile/member_avatar/' . $memberDetail->photo;
                  $Imageurlmember_avatar = ImageUpload::generatePreSignedUrl($member_avatarfolderName);
                ?>            
                <img class="rounded-circle w-100" alt="Image placeholder" src="{{ $Imageurlmember_avatar }}">
                @endif
              </div>
              @if($memberDetail->photo=='')
              <label class="profile_blocked">Note<sup class="required">*</sup>Upload picture otherwise the account will be blocked after 30 days</label>
              @endif
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-lg-6">
      <div class="card bg-white">
        <div class="card-body">
          <h3 class="card-title mb-3">Signature</h3>
          <div class="row">
            <div class="col-lg-12 ">
              <div class="signature_image_div">
                @if($memberDetail->signature=='')
                <label> <i class="fas fa-camera"></i>
                  <form enctype="multipart/form-data" id="signature_form" method="POST" action="{{ route('member.image') }}">
                    @csrf
                    <input type="hidden" name="member-id" id="member-id" value="{{ $memberDetail->id }}">
                    <input type="file" name="signature" class="signature_image" id="signature">
                  </form>
                </label>
                <img class="rounded-circle " alt="Image placeholder" src="{{url('/')}}/asset/images/signature-logo-design.png">
                @else
                <?php
                    $folderNamemember_signature = 'profile/member_signature/' . $memberDetail->signature;
                    $signatureurl = ImageUpload::generatePreSignedUrl($folderNamemember_signature);
                ?>
                <img class=" rounded-circle w-100" alt="Image placeholder" src="{{$signatureurl}}">
                @endif
              </div>
              @if($memberDetail->signature=='')
              <label class="profile_blocked">Note<sup class="required">*</sup>Upload signature otherwise the account will be blocked after 30 days</label>
              @endif
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endif
</div>
@stop
@section('script')
@include('templates.branch.member_management.partials.script')
@stop