@extends('templates.admin.master')

@section('content')
<div class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header header-elements-inline">
                    <h6 class="card-title font-weight-semibold">Create Branch</h6>
                </div>
                <form action="{{url('admin/branch-create')}}" method="post" id="branch-create">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Branch Name:<sup>*</sup></label>
                            <div class="col-lg-10">
                                <input type="text" id="branch-name" name="name" class="form-control" onkeyup="this.value = this.value.toUpperCase()" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-form-label col-lg-2">Branch Cash In Hand Limit:</label>
                            <div class="col-lg-10">
                                <input type="text" name="cash_in_hand" class="form-control" onkeypress="return isNumberKey(event)">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-form-label col-lg-12">Branch Location:<sup>*</sup></label>
                        </div>
                        <div class="form-group row">
                            <div class="col-lg-4">
                                <label class="col-form-label col-lg-12">Country</label>
                                <div class="col-lg-12">
                                    <select name="country" id="country" class="form-control">
                                        <option value="1" selected>India</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <label class="col-form-label col-lg-12">State</label>
                                <div class="col-lg-12">
                                    <select name="state" id="state" class="form-control">
                                        @foreach( \App\Models\States::select('id','name')->orderBy('name')->get() as $state )
                                        <option value="{{ $state->id }}">{{ $state->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <label class="col-form-label col-lg-12">City</label>
                                <div class="col-lg-12">
                                    <select name="city" id="city-create" class="form-control city">
                                        <option>--Select City--</option>
                                        @foreach( \App\Models\City::select('id','name','state_id')->orderBy('name')->where('state_id', 2)->get() as $city )
                                        <option value="{{ $city->id }}">{{ $city->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-lg-6">
                                <label class="col-form-label col-lg-12">Sector:<sup>*</sup></label>
                                <div class="col-lg-12">
                                    <input type="text" name="sector" class="form-control">
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <label class="col-form-label col-lg-12">Region:<sup>*</sup></label>
                                <div class="col-lg-12">
                                    <input type="text" name="regan" class="form-control">
                                </div>
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-lg-6">
                                <label class="col-form-label col-lg-12">Zone:<sup>*</sup></label>
                                <div class="col-lg-12">
                                    <input type="text" name="zone" class="form-control">
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <label class="col-form-label col-lg-12">Postal code:<sup>*</sup></label>
                                <div class="col-lg-12">
                                    <input type="text" name="pin_code" class="form-control">
                                </div>
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-lg-6">
                                <label class="col-form-label col-lg-12">Address:<sup>*</sup></label>
                                <div class="col-lg-12">
                                    <textarea name="address" class="form-control"></textarea>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <label class="col-form-label col-lg-12">Branch Phone Number:<sup>*</sup></label>
                                <div class="col-lg-12">
                                    <input type="text" name="phone" id="phonenumber" onkeypress="return isNumberKey(event)" maxlength="10" class="form-control">
                                </div>
                            </div>
                        </div>


                        <div class="form-group row">
                            <div class="col-lg-6">
                                <label class="col-form-label col-lg-12">Mail Id:</label>
                                <div class="col-lg-12">
                                    <input type="text" name="email" id="email" class="form-control" pattern="[a-zA-Z0-9!#$%&amp;'*+\/=?^_`{|}~.-]+@[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]+)*" autocomplete="off">
                                    <span id="error_email" class="error"></span>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <label class="col-form-label col-lg-12">Password: <sup>*</sup></label>
                                <div class="col-lg-12">
                                    <input type="password" name="password" id="password" class="form-control" autocomplete="off" required>
                                </div>
                            </div>
                        </div>


                        <div class="form-group row">
                            <div class="col-lg-6">
                                <label class="col-form-label col-lg-12">Confirm Password: <sup>*</sup></label>
                                <div class="col-lg-12">
                                    <input type="password" name="password_confirmation" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <label class="col-form-label col-lg-12">Login with OTP:</label>
                                <div class="col-lg-12">
                                    <input type="checkbox" name="checkotp" id="checkotp" class="form-control">
                                </div>
                            </div>
                        </div>

                        <h3>Assign Company</h3>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th scope="col">
                                    </th>
                                    <th scope="col">Company</th>
                                    <th scope="col">Old Business</th>
                                    <th scope="col">New Business</th>
                                    <th scope="col">Primary</th>
                                </tr>
                            </thead>
                            <tbody>

                                @if(!empty($allcompany))
                                @php $count = 1;
                                @endphp
                                <input type="hidden" name="csrf-token" value="{{csrf_token()}}">
                                @foreach($allcompany as $key => $com)
                                <tr>
                                    <th scope="row">
                                        <div class="form-check">
                                            <input class="form-check-input allcompanycheckcreate" data-id="{{$com->id}}" id="company_{{$com->id}}" name="company_chekbox[]" type="checkbox" value="{{$com->id}}">
                                        </div>
                                    </th>
                                    <td>{{$com->name}}</td>
                                    <td>
                                        <div class="form-check">
                                            <input class="form-check-input oldbusscheck oldvaluechecked oldcheckcreate_{{$com->id}}" data-id="old_{{$com->id}}" name="old_buss[]" type="checkbox" value="old_{{$com->id}}">
                                        </div>
                                    </td>
                                    <td>
                                        <div class="form-check">
                                            <input class="form-check-input newvaluechecked newcheckcreate_{{$com->id}}" data-id="new_{{$com->id}}" name="new_buss[]" type="checkbox" value="new_{{$com->id}}">
                                        </div>
                                    </td>
                                    <td>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" data-id="primary_{{$com->id}}" name="primarybox" value="primary_{{$com->id}}" id="primarybox">
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>

                    <input type="hidden" name="created_at" class="created_at" />
                    <div class="modal-footer">
                        <a href="{{ url()->previous() }}" type="button" class="btn btn-link" data-dismiss="modal">Back</a>
                        <button type="submit" id="brnach_submit" class="btn bg-dark">Submit<i class="icon-paperplane ml-2"></i></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    
</script>
@include('templates.admin.branch.partials.branch-script')
@stop