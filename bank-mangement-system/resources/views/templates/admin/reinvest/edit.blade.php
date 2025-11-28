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
                                <label class="col-form-label col-lg-2">Branch Name:</label>
                                <div class="col-lg-10">
                                    <input type="text" name="name" class="form-control" value="{{$branch->name}}" disabled>
                                    <input type="hidden" name="id" value="{{$branch->id}}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-form-label col-lg-12">Branch Location:</label>
                            </div>
                            <div class="form-group row">
                                <div class="col-lg-4">
                                    <label class="col-form-label col-lg-12">Country</label>
                                    <div class="col-lg-10">
                                        <select name="country" id="country" class="form-control" disabled="true">
                                            <option value="1" selected>India</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <label class="col-form-label col-lg-12">State</label>
                                    <div class="col-lg-10">
                                        <select name="state" id="state" class="form-control" disabled="true">
                                            @foreach( \App\Models\States::orderBy('name')->get() as $state )
                                                <option value="{{ $state->id }}" @if( $state->id == $branch->state_id) selected @endif>{{ $state->name
                                                                    }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <label class="col-form-label col-lg-12">City</label>
                                    <div class="col-lg-10">
                                        <select name="city" id="city" class="form-control city" disabled="true">
                                            @foreach( \App\Models\City::orderBy('name')->where('state_id', $branch->state_id)->get() as $city )
                                                <option value="{{ $city->id }}" @if( $city->id == $branch->city_id) selected @endif>{{ $city->name
                                                                    }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-lg-6">
                                    <label class="col-form-label col-lg-12">Zone/Sector:</label>
                                    <div class="col-lg-12">
                                        <input type="text" name="zone" class="form-control" value="{{$branch->zone}}">
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <label class="col-form-label col-lg-12">Postal code:</label>
                                    <div class="col-lg-12">
                                        <input type="text" name="pin_code" class="form-control" value="{{$branch->pin_code}}">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row">
                                <div class="col-lg-6">
                                    <label class="col-form-label col-lg-12">Address:</label>
                                    <div class="col-lg-10">
                                        <textarea name="address" class="form-control"> {{ $branch->address }}</textarea>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <label class="col-form-label col-lg-12">Branch Phone Number:</label>
                                    <div class="col-lg-10">
                                        <input type="text" name="phone" class="form-control" value="{{$branch->phone}}">
                                    </div>
                                </div>
                            </div>

                          {{--  <div class="form-group row">

                                <div class="col-lg-6">
                                    <label class="col-form-label col-lg-12">Password:</label>
                                    <input type="password" name="password" id="password" class="form-control" autocomplete="off">
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

@include('templates.admin.branch.partials.branch-edit-script')
@stop
