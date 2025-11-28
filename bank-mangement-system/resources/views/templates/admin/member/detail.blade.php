@extends('templates.admin.master')
@section('content')
    <div class="content">
        @if (isset($memberDetail->is_block) && $memberDetail->is_block == 0)
            <div class="d-flex mb-4">
                @if (check_my_permission(Auth::user()->id, '237') == '1')
                    <!-- <ul class="nav ">
               <li class="nav-item">
                <a href="{{ URL::to('admin/member-edit/' . $memberDetail->id . '') }}" class="btn btn-primary"><span>Edit</span></a>
                </li>
            </ul> -->
                @endif
                <!-- <ul class="nav ml-4">
               <li class="nav-item">
                <a href="{{ URL::to('admin/member/tds-certificate/' . $memberDetail->id . '') }}" class="btn btn-primary"><span>TDS Certificate</span></a>
                </li>
            </ul> -->
                @if (check_my_permission(Auth::user()->id, '238') == '1' ||
                        check_my_permission(Auth::user()->id, '239') == '1' ||
                        check_my_permission(Auth::user()->id, '240') == '1' ||
                        check_my_permission(Auth::user()->id, '241') == '1' ||
                        check_my_permission(Auth::user()->id, '242') == '1')
                    <!-- <ul class="nav  ml-4">
               <li class="nav-item dropdown">
            <a class="dropdown-toggle btn btn-primary" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
             More
            </a>
            <div class="dropdown-menu" aria-labelledby="navbarDropdown"> -->
                    <!-- @if (check_my_permission(Auth::user()->id, '238') == '1')
             @if ($idProofDetail->first_id_type_id == 5)
    <a class="dropdown-item" href="{{ URL::to('admin/form_g/' . $memberDetail->id . '') }}">Update From 15G/15H</a>
    @endif
            @endif -->
                    <!-- @if (check_my_permission(Auth::user()->id, '239') == '1')
    <a class="dropdown-item" href="{{ URL::to('admin/member-investment/' . $memberDetail->id . '') }}">Investment Plan</a>
    @endif
             @if (check_my_permission(Auth::user()->id, '240') == '1')
    <a class="dropdown-item" href="{{ URL::to('admin/member-loan/' . $memberDetail->id . '') }}">Loan Plan</a>
    @endif
             @if (check_my_permission(Auth::user()->id, '241') == '1')
    <a class="dropdown-item" href="{{ URL::to('admin/member/interest-tds/' . $memberDetail->id . '') }}">Interest & TDS Deduction</a>
    @endif
             @if (check_my_permission(Auth::user()->id, '242') == '1')
    <a class="dropdown-item" href="{{ URL::to('admin/member-transactions/' . $memberDetail->id . '') }}" target="_blank">Transaction</a>
    @endif -->
                    <!-- </li>
            </ul>
            @endif
            </ul>
             @if (check_my_permission(Auth::user()->id, '243') == '1')
            <ul class="nav ml-4"> -->
                    <!-- <li class="nav-item">
                @if ($memberDetail->is_blacklist_on_loan == 1)
    <button  class="btn btn-primary unblockUser"><span>Activate</span></button>
@else
    <button  class="btn btn-primary blockMemberOnLoan"><span>Blacklist For Loan</span></button>
    @endif
                </li> -->
                    <!-- </ul>
            @endif -->
            </div>
            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header header-elements-inline">
                            <h4 class="card-title font-weight-semibold">Customer Form Information</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <label class=" col-lg-4">Form No</label><label class=" col-lg-1">:</label>
                                <div class="col-lg-7  "> {{ $memberDetail->form_no }} </div>
                            </div>
                            <div class="row">
                                <label class=" col-lg-4">Join Date </label><label class=" col-lg-1">:</label>
                                <div class="col-lg-7">
                                    @if ($memberDetail->re_date)
                                        {{ date('d/m/Y', strtotime($memberDetail->re_date)) }}
                                    @endif
                                </div>
                            </div>
                            <div class="row">
                                <label class=" col-lg-4">Customer Id </label><label class=" col-lg-1">:</label>
                                <div class="col-lg-7  "> {{ $memberDetail->member_id }} </div>
                            </div>
                            @if ($_GET['type'] == 0)
                                <div class="row">
                                    <label class=" col-lg-4">Member Id </label><label class=" col-lg-1">:</label>
                                    <div class="col-lg-7  "> {{ $membercompany->member_id }} </div>
                                </div>
                                <!-- 
                                    <div class="row">
                                        <label class=" col-lg-4">Branch MI </label><label class=" col-lg-1">:</label>
                                        <div class="col-lg-7  "> {{ $membercompany->branch_mi }} </div>
                                    </div> 
                                -->
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
                    <div class="card">
                        <div class="card-header header-elements-inline">
                            <h4 class="card-title font-weight-semibold">Personal Information </h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <label class=" col-lg-4">First Name </label><label class=" col-lg-1">:</label>
                                <div class="col-lg-7  "> {{ $memberDetail->first_name }} </div>
                            </div>
                            <div class="row">
                                <label class=" col-lg-4">Last Name </label><label class=" col-lg-1">:</label>
                                <div class="col-lg-7  "> {{ $memberDetail->last_name }} </div>
                            </div>
                            <div class="  row">
                                <label class=" col-lg-4">Email Id </label><label class=" col-lg-1">:</label>
                                <div class="col-lg-7  "> {{ $memberDetail->email }} </div>
                            </div>
                            <div class="  row">
                                <label class=" col-lg-4">Mobile No</label><label class=" col-lg-1">:</label>
                                <div class="col-lg-7  "> {{ $memberDetail->mobile_no }} </div>
                            </div>
                            <div class="  row">
                                <label class=" col-lg-4">Date of Birth </label><label class=" col-lg-1">:</label>
                                <div class="col-lg-7  ">
                                    @if ($memberDetail->dob)
                                        {{ date('d/m/Y', strtotime(convertdate($memberDetail->dob))) }}
                                    @endif
                                </div>
                            </div>
                            <div class="  row">
                                <label class=" col-lg-4">Age</label><label class=" col-lg-1">:</label>
                                <div class="col-lg-7  "> {{ $memberDetail->age??calculateAge(date("Y-m-d", strtotime(convertdate($memberDetail->dob)))) }} </div>
                            </div>
                            <div class="  row">
                                <label class=" col-lg-4">Gender </label><label class=" col-lg-1">:</label>
                                <div class="col-lg-7  ">
                                    @if ($memberDetail->gender == 1)
                                        Male
                                    @else
                                        Female
                                    @endif
                                </div>
                            </div>
                            <div class="  row">
                                <label class=" col-lg-4">Occupation</label><label class=" col-lg-1">:</label>
                                <div class="col-lg-7  "> {{ getOccupationName($memberDetail->occupation_id) }} </div>
                            </div>
                            <div class="  row">
                                <label class=" col-lg-4">Annual Income </label><label class=" col-lg-1">:</label>
                                <div class="col-lg-7  "> {{ number_format($memberDetail->annual_income, 2, '.', ',') }}
                                    <img src="{{ url('/') }}/asset/images/rs.png" width="9"> </div>
                            </div>
                            <div class="  row">
                                <label class=" col-lg-4">Mother Name</label><label class=" col-lg-1">:</label>
                                <div class="col-lg-7  "> {{ $memberDetail->mother_name }} </div>
                            </div>
                            <div class="  row">
                                <label class=" col-lg-4">Father/ Husband's Name </label><label class=" col-lg-1">:</label>
                                <div class="col-lg-7  "> {{ $memberDetail->father_husband }}</div>
                            </div>
                            <div class="  row">
                                <label class=" col-lg-4">Marital status</label><label class=" col-lg-1">:</label>
                                <div class="col-lg-7  ">
                                    @if ($memberDetail->marital_status == 1)
                                        Married
                                    @else
                                        Un Married
                                    @endif
                                </div>
                            </div>
                            @if ($memberDetail->anniversary_date)
                                <div class="  row">
                                    <label class=" col-lg-4">Anniversary Date </label><label class=" col-lg-1">:</label>
                                    <div class="col-lg-7  ">
                                        @if ($memberDetail->anniversary_date != null)
                                            {{ date('d/m/Y', strtotime($memberDetail->anniversary_date)) }}
                                        @endif
                                    </div>
                                </div>
                            @endif
                            <div class="  row">
                                <label class=" col-lg-4">Religion</label><label class=" col-lg-1">:</label>
                                <div class="col-lg-7  ">
                                    @if ($memberDetail->religion_id > 0)
                                        {{ getReligionName($memberDetail->religion_id) }}
                                    @endif
                                </div>
                            </div>
                            <div class="  row">
                                <label class=" col-lg-4"> Categories </label><label class=" col-lg-1">:</label>
                                <div class="col-lg-7  ">
                                    @if ($memberDetail->special_category_id > 0)
                                        {{ getSpecialCategoryName($memberDetail->special_category_id) }}
                                    @else
                                        General Category
                                    @endif
                                </div>
                            </div>
                            <div class="  row">
                                <label class=" col-lg-4">Status </label><label class=" col-lg-1">:</label>
                                <div class="col-lg-7  ">
                                    @if ($memberDetail->status == 1)
                                        Active
                                    @else
                                        Inactive
                                    @endif
                                </div>
                            </div>
                            <h5 class="card-title mb-3">Residence Address</h5>
                            <div class="  row">
                                <label class=" col-lg-4">Address </label><label class=" col-lg-1">:</label>
                                <div class="col-lg-7  "> {{ $memberDetail->address }} </div>
                            </div>
                            <div class="  row">
                                <label class=" col-lg-4">State </label><label class=" col-lg-1">:</label>
                                <div class="col-lg-7  "> {{ getStateName($memberDetail->state_id) }} </div>
                            </div>
                            <div class="  row">
                                <label class=" col-lg-4">District </label><label class=" col-lg-1">:</label>
                                <div class="col-lg-7  "> {{ getDistrictName($memberDetail->district_id) }} </div>
                            </div>
                            <div class="  row">
                                <label class=" col-lg-4">City </label><label class=" col-lg-1">:</label>
                                <div class="col-lg-7  "> {{ getCityName($memberDetail->city_id) }} </div>
                            </div>
                            <div class="  row">
                                <label class=" col-lg-4">Village Name </label><label class=" col-lg-1">:</label>
                                <div class="col-lg-7  ">{{ $memberDetail->village }}</div>
                            </div>
                            <div class="  row">
                                <label class=" col-lg-4">Pin Code </label><label class=" col-lg-1">:</label>
                                <div class="col-lg-7  "> {{ $memberDetail->pin_code }} </div>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header header-elements-inline">
                            <h4 class="card-title font-weight-semibold">Nominee Information</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <label class=" col-lg-4">Full Name </label><label class=" col-lg-1">:</label>
                                <div class="col-lg-7  "> {{ $nomineeDetail->name }} </div>
                            </div>
                            <div class="row">
                                <label class=" col-lg-4">Relationship</label><label class=" col-lg-1">:</label>
                                <div class="col-lg-7  ">
                                    @if ($nomineeDetail->relation > 0)
                                        {{ getRelationsName($nomineeDetail->relation) }}
                                    @endif
                                </div>
                            </div>
                            <div class="row">
                                <label class=" col-lg-4">Gender</label><label class=" col-lg-1">:</label>
                                <div class="col-lg-7  ">
                                    @if ($nomineeDetail->gender == 1)
                                        Male
                                    @else
                                        Female
                                    @endif
                                </div>
                            </div>
                            <input type="hidden" id="sdate" class="create_application_date">
                            <div class="row">
                                <label class=" col-lg-4">Date of Birth</label><label class=" col-lg-1">:</label>
                                <div class="col-lg-7  ">
                                    @if ($nomineeDetail->dob)
                                        {{ date('d/m/Y', strtotime(convertdate($nomineeDetail->dob))) }}
                                    @endif
                                </div>
                            </div>
                            <div class="row">
                                <label class=" col-lg-4">Age</label><label class=" col-lg-1">:</label>
                                <div class="col-lg-7  "> {{ $nomineeDetail->age??calculateAge(date("Y-m-d", strtotime(convertdate($nomineeDetail->dob)))) }} </div>
                            </div>
                            <div class="row">
                                <label class=" col-lg-4">Mobile No</label><label class=" col-lg-1">:</label>
                                <div class="col-lg-7  "> {{ $nomineeDetail->mobile_no }} </div>
                            </div>
                            <div class="row">
                                <label class=" col-lg-4">Is Minor</label><label class=" col-lg-1">:</label>
                                <div class="col-lg-7  ">
                                    @if ($nomineeDetail->is_minor == 1)
                                        Yes
                                    @else
                                        No
                                    @endif
                                </div>
                            </div>
                            @if ($nomineeDetail->is_minor == 1)
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
                    <div class="card">
                        <div class="card-header header-elements-inline">
                            <h4 class="card-title font-weight-semibold">Bank Information</h4>
                        </div>
                        <div class="card-body">
                            @if ($bankDetail)
                                <div class="  row">
                                    <label class=" col-lg-4">Bank Name </label><label class=" col-lg-1">:</label>
                                    <div class="col-lg-7  "> {{ $bankDetail->bank_name }} </div>
                                </div>
                                <div class="  row">
                                    <label class=" col-lg-4">Branch Name</label><label class=" col-lg-1">:</label>
                                    <div class="col-lg-7  "> {{ $bankDetail->branch_name }} </div>
                                </div>
                                <div class="  row">
                                    <label class=" col-lg-4">Bank A/C No </label><label class=" col-lg-1">:</label>
                                    <div class="col-lg-7  "> {{ $bankDetail->account_no }}</div>
                                </div>
                                <div class="  row">
                                    <label class=" col-lg-4">IFSC Code</label><label class=" col-lg-1">:</label>
                                    <div class="col-lg-7  ">{{ $bankDetail->ifsc_code }}</div>
                                </div>
                                <div class="  row">
                                    <label class=" col-lg-4">Bank Address</label><label class=" col-lg-1">:</label>
                                    <div class="col-lg-7  ">{{ $bankDetail->address }} </div>
                                </div>
                            @else
                                <div class="row">
                                    <div class="col-lg-12"> Bank detail not found! </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header header-elements-inline">
                            <h4 class="card-title font-weight-semibold">Associate Details</h4>
                        </div>
                        <div class="card-body">
                            @if ($_GET['type'] == 1)
                                <div class="row">
                                    <label class=" col-lg-4">Associate Code </label><label class=" col-lg-1">:</label>
                                    <div class="col-lg-7  ">
                                        @if ($memberDetail->associate_id == 0)
                                            Member Associate With Admin
                                        @else
                                            {{ $memberDetail['children']->associate_no }}
                                        @endif
                                    </div>
                                </div>
                                @if ($memberDetail->associate_id != 0)
                                    <div class="row">
                                        <label class=" col-lg-4">Associate Name </label><label class=" col-lg-1">:</label>
                                        <div class="col-lg-7  "> {{ $memberDetail['children']->first_name }}
                                            {{ $memberDetail['children']->last_name }} </div>
                                    </div>
                                    <div class="row">
                                        <label class=" col-lg-4">Customer ID </label><label class=" col-lg-1">:</label>
                                        <div class="col-lg-7  "> {{ $memberDetail['children']->member_id }} </div>
                                    </div>
                                @endif
                            @else
                                <div class="row">
                                    <label class=" col-lg-4">Associate Code </label><label class=" col-lg-1">:</label>
                                    <div class="col-lg-7  ">
                                        @if ($membercompany->associate_id == 0)
                                            Member Associate With Admin
                                        @else
                                            {{ $membercompany['memberAssociate']->associate_no }}
                                        @endif
                                    </div>
                                </div>
                                @if ($membercompany->associate_id != 0)
                                    <div class="row">
                                        <label class=" col-lg-4">Associate Name </label><label class=" col-lg-1">:</label>
                                        <div class="col-lg-7  "> {{ $membercompany['memberAssociate']->first_name }}
                                            {{ $membercompany['memberAssociate']->last_name }} </div>
                                    </div>
                                    <div class="row">
                                        <label class=" col-lg-4">Customer ID </label><label class=" col-lg-1">:</label>
                                        <div class="col-lg-7  "> {{ $membercompany['memberAssociate']->member_id }} </div>
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header header-elements-inline">
                            <h6 class="card-title font-weight-semibold">Profile Image</h6>
                        </div>
                        <div class="card-body text-center">
                            <div class="card-img-actions d-inline-block mb-3">
                                <div class="profile_image_div">
                                    <label> <i class="fas fa-camera"></i>
                                        <form enctype="multipart/form-data" id="upload_form" method="POST"
                                            action="{{ route('admin.member.image') }}">
                                            @csrf
                                            <input type="hidden" name="member-id" id="member-id"
                                                value="{{ $memberDetail->id }}">
                                            <input type="file" name="photo" class="profile_image" id="photo">
                                        </form>
                                    </label>
                                    {{--
                                    @if ($memberDetail->photo == '')
                                        <img class="img-fluid rounded-circle" id="photo-preview" src="{{ url('/') }}/asset/images/user.png">
                                    @else
                                        <img class="img-fluid rounded-circle" id="photo-preview" src="{{ ImageUpload::generatePreSignedUrl('profile/member_avatar/' . $memberDetail->photo) }}">
                                    @endif
                                    --}}
                                    @if($memberDetail->photo=='')
                                        <?php
                                            $member_avatar_photo_url = url('/')."/asset/images/user.png";
                                        ?>
                                    @else                              
                                        <?php
                                        $foldermember_avatarName = 'profile/member_avatar/' . $memberDetail->photo;
                                        if (ImageUpload::fileExists($foldermember_avatarName) && $memberDetail->photo != '') {
                                            $member_avatar_photo_url = ImageUpload::generatePreSignedUrl($foldermember_avatarName);
                                        } else {
                                            $member_avatar_photo_url = url('/')."/asset/images/user.png";
                                        }
                                        ?>
                                    @endif
                                    <img class="img-fluid rounded-circle" id="photo-preview"  src="{{$member_avatar_photo_url}}">
                                </div>
                                <div class="form-group">
                                    @if ($memberDetail->photo == '')
                                        <label class="profile_blocked">Note<sup class="required">*</sup>
                                            Upload picture otherwise the account will be blocked after 30 days
                                        </label>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header header-elements-inline">
                            <h6 class="card-title font-weight-semibold">Signature</h6>
                        </div>
                        <div class="card-body text-center">
                            <div class="card-img-actions d-inline-block mb-3">
                                <div class="signature_image_div">
                                    {{--
                                    @if ($memberDetail->signature == '')
                                        <img class="Image placeholder w-100" id="signature-preview" src="{{ url('/') }}/asset/images/signature-logo-design.png">
                                    @else
                                        <img class="Image placeholder w-100" id="signature-preview" src="{{ ImageUpload::generatePreSignedUrl('profile/member_signature/' . $memberDetail->signature) }}">
                                    @endif
                                    --}}
                                    @if($memberDetail->signature=='')
                                    <?php $member_signature_signature_url = url('/') . "/asset/images/signature-logo-design.png"; ?>
                                    @else
                                    <?php
                                        $member_signaturefolderName = 'profile/member_signature/' . $memberDetail->signature;
                                        if (ImageUpload::fileExists($member_signaturefolderName) && $memberDetail->signature != '') {
                                            $member_signature_signature_url = ImageUpload::generatePreSignedUrl($member_signaturefolderName);
                                        } else {
                                            $member_signature_signature_url = url('/')."/asset/images/signature-logo-design.png";
                                        }
                                        ?>
                                        <img class="Image placeholder w-100" id="signature-preview" src="{{$member_signature_signature_url}}">
                                    @endif
                                    <label>
                                        <form enctype="multipart/form-data" id="signature_form" method="POST"
                                            action="{{ route('admin.member.image') }}">
                                            @csrf
                                            <!--
                                                <input type="hidden" name="member-id" id="member-id" value="{{ $memberDetail->id }}">
                                                <input type="file" name="signature" class="signature_image" id="signature">
                                            -->
                                            <div class="custom-file error-msg">
                                                <input type="hidden" name="member-id" id="member-id"
                                                    value="{{ $memberDetail->id }}">
                                                <input type="file" name="signature"
                                                    class="signature_image custom-file-input" id="signature">
                                                <label class="custom-file-label" for="signature">Select document</label>
                                            </div>
                                        </form>
                                    </label>
                                </div>
                            </div>
                            <!--
                            <div class="form-group">
                                @if ($memberDetail->signature == '')
    <label class="profile_blocked">Note<sup class="required">*</sup>Upload signature otherwise the account will be blocked after 30 days
                                  </label>
    @endif
                            </div>
    -->
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header header-elements-inline">
                            <h5 class="card-title font-weight-semibold">ID Proof</h5>
                        </div>
                        <div class="card-body ">
                            <h6 class="card-title font-weight-semibold">First ID Proof </h6>
                            <div class="row">
                                <label class=" col-lg-4">ID Proof Document Type </label><label class=" col-lg-1">:</label>
                                <div class="col-lg-7  "> {{ getIdProofName($idProofDetail->first_id_type_id) }} </div>
                            </div>
                            <div class="row">
                                <label class=" col-lg-4">ID No </label><label class=" col-lg-1">:</label>
                                <div class="col-lg-7  ">{{ $idProofDetail->first_id_no }}</div>
                            </div>
                            <div class="row">
                                <label class=" col-lg-4">Address </label><label class=" col-lg-1">:</label>
                                <div class="col-lg-7  ">{{ $idProofDetail->first_address }}</div>
                            </div>
                            <h6 class="card-title font-weight-semibold">Second ID Proof (Address Proof)</h6>
                            <div class="row">
                                <label class=" col-lg-4">ID Proof Document Type </label><label class=" col-lg-1">:</label>
                                <div class="col-lg-7  ">{{ getIdProofName($idProofDetail->second_id_type_id) }}</div>
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
                </div>
            </div>
        @else
            <div class="row">
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header header-elements-inline">
                            <h3 class="card-title font-weight-semibold">Profile Image</h3>
                        </div>
                        <div class="card-body">
                            <div class="card-img-actions d-inline-block mb-3 w-100">
                                <div class="profile_image_div">
                                    <label> <i class="fas fa-camera"></i>
                                        <form enctype="multipart/form-data" id="upload_form" method="POST"
                                            action="{{ route('admin.member.image') }}">
                                            @csrf
                                            <input type="hidden" name="created_at" class="created_at">
                                            <input type="hidden" name="member-id" id="member-id"
                                                value="{{ $memberDetail->id }}">
                                            <input type="file" name="photo" class="profile_image" id="photo">
                                        </form>
                                    </label>
                                    @if ($memberDetail->photo == '')
                                        <img class="img-fluid" id="photo-preview"
                                            src="{{ url('/') }}/asset/images/user.png">
                                    @else
                                        {{-- <img class="img-fluid" id="photo-preview" src="{{url('/')}}/asset/profile/member_avatar/{{ $memberDetail->photo }}"> --}}
                                        <img class="img-fluid" id="photo-preview"
                                            src="{{ ImageUpload::generatePreSignedUrl('profile/member_avatar/' . $memberDetail->photo) }}">
                                    @endif
                                </div>
                            </div>
                            <div class="form-group">
                                @if ($memberDetail->photo == '')
                                    <label class="profile_blocked">Note<sup class="required">*</sup>
                                        Upload picture otherwise the account will be blocked after 30 days
                                    </label>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header header-elements-inline">
                            <h3 class="card-title font-weight-semibold">Signature</h3>
                        </div>
                        <div class="card-body">
                            <div class="card-img-actions d-inline-block mb-3">
                                <div class="signature_image_div">
                                    <label> <i class="fas fa-camera"></i>
                                        <form enctype="multipart/form-data" id="signature_form" method="POST"
                                            action="{{ route('admin.member.image') }}">
                                            @csrf
                                            <input type="hidden" name="created_at" class="created_at">
                                            <input type="hidden" name="member-id" id="member-id" value="{{ $memberDetail->id }}">
                                            <input type="file" name="signature" class="signature_image" id="signature">
                                        </form>
                                    </label>
                                    {{--
                                    @if ($memberDetail->signature == '')
                                        <img class="img-fluid" id="signature-preview" src="{{ url('/') }}/asset/images/signature-logo-design.png">
                                    @else
                                        <img class="img-fluid" id="signature-preview" src="{{ ImageUpload::generatePreSignedUrl('profile/member_signature/' . $memberDetail->signature) }}">
                                    @endif
                                    --}}
                                    @if($memberDetail->signature=='')
                                    <img class="Image placeholder w-100"  id="signature-preview" src="{{url('/')}}/asset/images/signature-logo-design.png">
                                    @else
                                    <?php
                                        $foldermember_signatureName = 'profile/member_signature/' . $memberDetail->signature;
                                        if (ImageUpload::fileExists($foldermember_signatureName) && $memberDetail->signature != '') {
                                            $signature_url = ImageUpload::generatePreSignedUrl($foldermember_signatureName);
                                        } else {
                                            $signature_url = url('/')."/asset/images/signature-logo-design.png";
                                        }
                                        ?>
                                        <img class="Image placeholder w-100" id="signature-preview" src="{{$signature_url}}">
                                    @endif
                                </div>
                            </div>
                            <div class="form-group">
                                @if ($memberDetail->signature == '')
                                    <label class="profile_blocked">Note<sup class="required">*</sup>Upload signature
                                        otherwise the account will be blocked after 30 days
                                    </label>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
    @include('templates.admin.member.partials.script')
@stop