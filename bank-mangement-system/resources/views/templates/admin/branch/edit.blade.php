@extends('templates.admin.master')
@section('content')
<div class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header header-elements-inline">
                    <h6 class="card-title font-weight-semibold">Edit Branch</h6>
                </div>
                <form action="{{route('branch.update')}}" method="post" id="branch-update">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Branch Name:<sup>*</sup></label>
                            <div class="col-lg-12">
                                <input type="text" name="name" class="form-control" value="{{$branch->name}}" readonly>
                                <input type="hidden" name="id" value="{{$branch->id}}">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Branch Cash In Hand Limit:</label>
                            <div class="col-lg-12">
                                <input type="text" name="cash_in_hand" class="form-control" onkeypress="return isNumberKey(event)" value="{{$branch->cash_in_hand}}">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-form-label col-lg-12">Branch Location:</label>
                        </div>
                        <div class="form-group row">
                            <div class="col-lg-4">
                                <label class="col-form-label col-lg-12">Country</label>
                                <div class="col-lg-12">
                                    <select name="country" id="country" class="form-control" disabled="true">
                                        <option value="1" selected>India</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <label class="col-form-label col-lg-12">State</label>
                                <div class="col-lg-12">
                                    <select name="state" id="state" class="form-control" disabled="true">
                                        @foreach( \App\Models\States::select('id','name')->orderBy('name')->get() as $state )
                                        <option value="{{ $state->id }}" @if( $state->id == $branch->state_id) selected @endif>{{ $state->name
                                                                    }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <label class="col-form-label col-lg-12">City</label>
                                <div class="col-lg-12">
                                    <select name="city" id="city" class="form-control city" disabled="true">
                                        @foreach( \App\Models\City::select('id','name','state_id')->orderBy('name')->where('state_id', $branch->state_id)->get() as $city )
                                        <option value="{{ $city->id }}" @if( $city->id == $branch->city_id) selected @endif>{{ $city->name
                                                                    }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-lg-6">
                                <label class="col-form-label col-lg-12">Sector:<sup>*</sup></label>
                                <div class="col-lg-12">
                                    <input type="text" name="sector" class="form-control" value="{{$branch->sector}}">
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <label class="col-form-label col-lg-12">Region:<sup>*</sup></label>
                                <div class="col-lg-12">
                                    <input type="text" name="regan" class="form-control" value="{{$branch->regan}}">
                                </div>
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-lg-6">
                                <label class="col-form-label col-lg-12">Zone:<sup>*</sup></label>
                                <div class="col-lg-12">
                                    <input type="text" name="zone" class="form-control" value="{{$branch->zone}}">
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <label class="col-form-label col-lg-12">Postal code:<sup>*</sup></label>
                                <div class="col-lg-12">
                                    <input type="text" name="pin_code" class="form-control" value="{{$branch->pin_code}}">
                                </div>
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-lg-6">
                                <label class="col-form-label col-lg-12">Address:<sup>*</sup></label>
                                <div class="col-lg-12">
                                    <textarea name="address" class="form-control"> {{ $branch->address }}</textarea>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <label class="col-form-label col-lg-12">Branch Phone Number:<sup>*</sup></label>
                                <div class="col-lg-12">
                                    <input type="text" name="phone" class="form-control" onkeypress="return isNumberKey(event)" maxlength="10" value="{{$branch->phone}}">
                                </div>
                            </div>
                        </div>

                        <div class="form-group row">

                            <div class="col-lg-6">
                                <label class="col-form-label col-lg-12">Mail Id:</label>
                                <div class="col-lg-12">
                                    <input type="email" name="email" id="email" class="form-control" autocomplete="off" value="{{$branch->email}}">
                                    <span id="error_email" class="error"></span>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <label class="col-form-label col-lg-12">Login with OTP:</label>
                                <div class="col-lg-12">
                                    <input type="checkbox" name="checkotp" id="checkotp" @if($branch->otp_login == 0) checked @endif class="form-control">
                                </div>
                            </div>


                        </div>

                        {{-- <div class="form-group row">

                                <div class="col-lg-6">
                                    <label class="col-form-label col-lg-12">Password: <sup>*</sup></label>
                                    <input type="password" name="password" id="password" class="form-control" autocomplete="off" required>
                                </div>

                                <div class="col-lg-6">
                                    <label class="col-form-label col-lg-12">Confirm Password:</label>
                                    <input type="password" name="password_confirmation" class="form-control">
                                </div>

                        </div>--}}
                    </div>
                    <div class="modal-footer">
                        <a href="{{ url()->previous() }}" type="button" class="btn btn-link" data-dismiss="modal">Back</a>
                        <button type="submit" class="btn bg-dark">Update<i class="icon-paperplane ml-2"></i></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    function isNumberKey(evt) {
        var charCode = (evt.which) ? evt.which : evt.keyCode;
        if (charCode != 46 && charCode > 31 &&
            (charCode < 48 || charCode > 57))
            return false;

        return true;
    }    
    $(document).ready(function(){
        $("#email").keyup(function() {
            var email = $("#email").val();
            var filter = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
            if (!filter.test(email)) {
                $("#error_email").text("Email id is not valid");
                $("#email").focus();
            } else {
                $("#error_email").text("");
            }
        });
    });
</script>
@include('templates.admin.branch.partials.branch-edit-script')
@stop