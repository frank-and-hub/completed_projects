@extends('master')

@section('content')
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">Branch</h6>
                            <div class="header-elements">
                                <a class="font-weight-semibold" data-toggle="modal" data-target="#create"><i class="icon-file-plus mr-2"></i>Create Branch</a>
                            </div>
                    </div>
                    <table class="table datatable-show-all">
                        <thead>
                            <tr>
                                <th>S/N</th>
                                <th>Name</th>
                                <th>Country</th>
                                <th>State</th>
                                <th>Mobile</th>
                                <th>Zipcode</th>
                                <th>Postal code</th>
                                <th>Address</th>
                                <th class="text-center">Action</th>    
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($branch as $k=>$val)
                            <tr>
                                <td>{{++$k}}.</td>
                                <td>{{$val->name}}</td>
                                <td>{{$val->country}}</td>
                                <td>{{$val->state}}</td>
                                <td>{{$val->mobile}}</td>
                                <td>{{$val->zip_code}}</td>
                                <td>{{$val->postal_code}}</td>
                                <td>{{$val->address}}</td>
                                <td class="text-center">
                                    <div class="list-icons">
                                        <div class="dropdown">
                                            <a href="#" class="list-icons-item" data-toggle="dropdown">
                                                <i class="icon-menu9"></i>
                                            </a>
                                            <div class="dropdown-menu dropdown-menu-right">
                                                <a data-toggle="modal" data-target="#{{$val->id}}update" class="dropdown-item"><i class="icon-pencil7 mr-2"></i>Edit</a>
                                                <a data-toggle="modal" data-target="#{{$val->id}}delete" class="dropdown-item"><i class="icon-bin2 mr-2"></i>Delete</a>
                                            </div>
                                        </div>
                                    </div>
                                </td>                   
                            </tr>
                            <div id="{{$val->id}}delete" class="modal fade" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">   
                                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                                        </div>
                                        <div class="modal-body">
                                            <h6 class="font-weight-semibold">Are you sure you want to delete this?</h6>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-link" data-dismiss="modal">Close</button>
                                            <a  href="{{url('/')}}/admin/branch/delete/{{$val->id}}" class="btn bg-danger">Proceed</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div id="{{$val->id}}update" class="modal fade" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">   
                                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                                        </div>
                                        <form action="{{route('branch.update')}}" method="post">
                                        @csrf
                                            <div class="modal-body">
                                                <div class="form-group row">
                                                    <label class="col-form-label col-lg-2">Branch Name:</label>
                                                    <div class="col-lg-10">
                                                        <input type="text" name="name" class="form-control" value="{{$val->name}}">
                                                        <input type="hidden" name="id" value="{{$val->id}}">
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label class="col-form-label col-lg-12">Branch Location:</label>
                                                </div>
                                                <div class="form-group row">
                                                    <div class="col-lg-6">
                                                        <label class="col-form-label col-lg-12">State</label>
                                                        <div class="col-lg-12">
                                                            <select name="state" id="state" class="form-control">
                                                                @foreach( \App\Models\States::orderBy('name')->get() as $state )
                                                                    <option value="{{ $state->id }}" @if( $state->id == $val->state_id) selected @endif>{{ $state->name
                                                                    }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-6">
                                                        <label class="col-form-label col-lg-12">City</label>
                                                        <div class="col-lg-12">
                                                            <select name="city" id="city" class="form-control">
                                                                @foreach( \App\Models\City::orderBy('name')->get() as $city )
                                                                    <option value="{{ $city->id }}" @if( $city->id == $val->city_id) selected @endif>{{ $city->name
                                                                    }}</option>
                                                                @endforeach
                                                            </select>
                                                            <input type="hidden" name="country" value="1" class="form-control">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <div class="col-lg-6">
                                                        <label class="col-form-label col-lg-12">Zone/Sector:</label>
                                                        <div class="col-lg-12">
                                                            <input type="text" name="zone" class="form-control" value="{{$val->zone}}">
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-6">
                                                        <label class="col-form-label col-lg-12">Pin code:</label>
                                                        <div class="col-lg-12">
                                                            <input type="text" name="pin_code" class="form-control" value="{{$val->pin_code}}">
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="form-group row">
                                                    <label class="col-form-label col-lg-2">Address:</label>
                                                    <div class="col-lg-10">
                                                        <textarea name="address" class="form-control"> {{ $val->address }}</textarea>
                                                    </div>
                                                </div>

                                                <div class="form-group row">
                                                    <label class="col-form-label col-lg-6">Branch Phone Number:</label>
                                                    <div class="col-lg-6">
                                                        <input type="text" name="phone" class="form-control" value="{{$val->phone}}">
                                                    </div>
                                                </div>

{{--
                                                <div class="form-group row">
                                                    <label class="col-form-label col-lg-2">State</label>
                                                    <div class="col-lg-10">
                                                        <input type="text" name="state" class="form-control" value="{{$val->state}}">
                                                    </div>
                                                </div>                                                  
                                                <div class="form-group row">
                                                    <label class="col-form-label col-lg-2">Mobile:</label>
                                                    <div class="col-lg-10">
                                                        <input type="text" name="mobile" class="form-control" value="{{$val->mobile}}">
                                                    </div>
                                                </div>                                                  
                                                <div class="form-group row">
                                                    <label class="col-form-label col-lg-2">Zip code:</label>
                                                    <div class="col-lg-10">
                                                        <input type="text" name="zip_code" class="form-control" value="{{$val->zip_code}}">
                                                    </div>
                                                </div>                                                  
                                                <div class="form-group row">
                                                    <label class="col-form-label col-lg-2">Postal code:</label>
                                                    <div class="col-lg-10">
                                                        <input type="text" name="postal_code" class="form-control" value="{{$val->postal_code}}">
                                                    </div>
                                                </div>                                                  
                                                <div class="form-group row">
                                                    <label class="col-form-label col-lg-2">Address:</label>
                                                    <div class="col-lg-10">
                                                        <input type="text" name="address" class="form-control" value="{{$val->address}}">
                                                    </div>
                                                </div> --}}
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-link" data-dismiss="modal">Close</button>
                                                <button type="submit" class="btn bg-dark">Update<i class="icon-paperplane ml-2"></i></button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            @endforeach               
                        </tbody>                    
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div id="create" class="modal fade" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">   
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <form action="{{url('admin/branch-create')}}" method="post">
                @csrf
                    <div class="modal-body">
                    <div class="form-group row">
                        <label class="col-form-label col-lg-2">Branch Name:</label>
                        <div class="col-lg-10">
                            <input type="text" name="name" class="form-control">
                        </div>
                    </div>  
                    <div class="form-group row">
                        <label class="col-form-label col-lg-12">Branch Location:</label>
                    </div>                                                  
                    <div class="form-group row">
                        <div class="col-lg-6">
                        <label class="col-form-label col-lg-2">State</label>
                        <div class="col-lg-10">
                                <select name="state" id="state" class="form-control">
                                    @foreach( \App\Models\States::orderBy('name')->get() as $state )
                                    <option value="{{ $state->id }}">{{ $state->name }}</option>
                                    @endforeach
                                </select>
                        </div>
                    </div>                                                  
                        <div class="col-lg-6">
                        <label class="col-form-label col-lg-2">City</label>
                        <div class="col-lg-10">
                            <select name="city" id="city" class="form-control">
                                @foreach( \App\Models\City::orderBy('name')->get() as $city )
                                    <option value="{{ $city->id }}">{{ $city->name }}</option>
                                @endforeach
                            </select>
                            <input type="hidden" name="country" value="1" class="form-control">
                        </div>
                        </div>
                    </div>                                                  

                    <div class="form-group row">
                            <div class="col-lg-6">
                                <label class="col-form-label col-lg-2">Zone/Sector:</label>
                        <div class="col-lg-10">
                                    <input type="text" name="zone" class="form-control">
                        </div>
                    </div>                                                  
                            <div class="col-lg-6">
                                <label class="col-form-label col-lg-2">Pin code:</label>
                        <div class="col-lg-10">
                                    <input type="text" name="pin_code" class="form-control">
                                </div>
                        </div>
                    </div>                                                  

                    <div class="form-group row">
                        <label class="col-form-label col-lg-2">Address:</label>
                        <div class="col-lg-10">
                                <textarea name="address" class="form-control"></textarea>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-form-label col-lg-6">Branch Phone Number:</label>
                            <div class="col-lg-6">
                                <input type="text" name="phone" class="form-control">
                        </div>
                    </div>               
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-link" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn bg-dark">Submit<i class="icon-paperplane ml-2"></i></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@include('templates.admin.web-control.partials.branch-script')
@stop
