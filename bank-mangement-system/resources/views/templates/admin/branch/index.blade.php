@extends('templates.admin.master')

@section('content')

<div class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{url('admin/branch/active-deactive')}}" method="post" enctype="multipart/form-data" name="activate-deactivate-branch" id="activate-deactivate-branch">
                        @csrf
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12 font-weight-semibold">Activate/Deactivate Branches<sup>*</sup></label>
                                    <div class="col-lg-12 error-msg">
                                        <div class="input-group">
                                            <select class="form-control" id="activate_deactive_all" name="activate_deactive_all" required>
                                                <option value="">Please Select</option>
                                                <option value="1">Activate</option>
                                                <option value="0">Deactivate</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group row ">
                                    <div class="col-lg-12 text-center">
                                        <input type="submit" name="submit_form" class=" btn bg-dark legitRipple" value="Submit">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group row">
                                <label class="col-form-label col-lg-12 font-weight-semibold">OTP Status</label>
                                <div class="col-lg-12 error-msg">
                                    <div class="input-group">
                                        <select class="form-control" id="otp_status" name="otp_status">
                                            <option value="" selected>Select Option</option>
                                            <option value="0">ON</option>
                                            <option value="1">OFF</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group row">
                                <label class="col-form-label col-lg-12 font-weight-semibold">Remove Cash Limit</label>
                                <div class="col-lg-12 error-msg">
                                    <div class="input-group">
                                        <select class="form-control" id="cash_limit" name="cash_limit">
                                            <option value="" selected>Select Option</option>
                                            <option value="0">Remove Cash Limit</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-12">
            <div class="card">
                <div class="card-header header-elements-inline">
                    <h6 class="card-title font-weight-semibold">Branch</h6>
                    <div class="header-elements">
                        {{--<a class="font-weight-semibold" href="{{ route('branch.create') }}"><i class="icon-file-plus mr-2"></i>Create Branch</a>--}}
                    </div>
                </div>
                <table class="table datatable-show-all" id="branch">
                    <thead>
                        <tr>
                            <th width="5%">S/N</th>
                            <th width="5%">Limit</th>
                            <th width="5%">Balance Amount</th>
                            <th width="10%">Created Date</th>
                            <th width="5%">Branch Code</th>
                            <th width="10%">Branch Name</th>
                            <th width="30%">Sector</th>
                            <th width="30%">Region</th>
                            <th width="30%">Zone</th>
                            <th width="5%">City</th>
                            <th width="5%">State</th>
                            <th width="10%">Phone Number</th>
                            <th width="10%">Email Id</th>
                            <th width="10%">Otp</th>
                            <th width="10%">Status</th>
                            <th width="30%">Address</th>
                            <th class="text-center" width="10%">Action</th>
                        </tr>
                    </thead>
                   
                </table>
                <div class="assigend_model"></div>
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
            <form action="{{url('admin/branch-create')}}" method="post" id="branch-create">
                @csrf
                <div class="modal-body">
                    <div class="form-group row">
                        <label class="col-form-label col-lg-2">Branch Name:</label>
                        <div class="col-lg-10">
                            <input type="text" id="branch-name" name="name" class="form-control" onkeyup="this.value = this.value.toUpperCase()" required>
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
                                    @foreach( \App\Models\States::select('id','name')->orderBy('name')->get() as $state )
                                    <option value="{{ $state->id }}">{{ $state->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <label class="col-form-label col-lg-2">City</label>
                            <div class="col-lg-10">
                                <select name="city" id="city-create" class="form-control city">
                                    <option>--Select City--</option>
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
<input type="hidden" class="totallength">
<!-- <div class="modal fade" id="assignModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Company Assigend</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th scope="col">
                                <div class="form-check">
                                    <input class="form-check-input" id="checkall" type="checkbox" value="1">
                                </div>
                            </th>
                            <th scope="col">Company</th>
                            <th scope="col">Old Business</th>
                            <th scope="col">New Business</th>
                            <th scope="col">Primary</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(!empty($company))
                        @php $count = 1;
                        @endphp
                        <input type="hidden" name="branch_id" id="branch_id">
                        <input type="hidden" name="csrf-token" value="{{csrf_token()}}">
                        @foreach($company as $key => $com)
                        <tr>
                            <th scope="row">
                                <div class="form-check">
                                    <input class="form-check-input allcompanycheck" data-id="{{$com}}"  id="company_{{$key}}" type="checkbox" value="{{$key}}">
                                </div>
                            </th>
                            <td>{{$com}}</td>
                            <td>
                                <div class="form-check">
                                    <input class="form-check-input oldvaluechecked oldcheck_{{$key}}" data-id="old_{{$key}}" type="checkbox" value="1">
                                </div>
                            </td>
                            <td>
                                <div class="form-check">
                                    <input class="form-check-input newvaluechecked newcheck_{{$key}}" data-id="new_{{$key}}" type="checkbox" value="1">
                                </div>
                            </td>
                            <td>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" data-id="primary_{{$key}}" name="primarybox" id="primarybox">
                                </div>
                            </td>
                        </tr>
                        @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <a href="javascript:void(0)" class="btn btn-primary" id="assignsubmit">Submit</a>
            </div>
        </div>
    </div>
</div> -->
<!-- Modal -->
<div class="modal fade" id="branchModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Change Branch Name</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="col-md-12">
                    <div class="form-group row">
                        <label class="col-form-label col-lg-12">New Branch Name <sup>*</sup></label>
                        <div class="col-lg-12 error-msg">
                            <div class="input-group">
                                <input type="hidden" name="branch_id" id="u_branch_id">
                                <input type="text" name="branch_name" id="branch_name" class="form-control" >

                            </div>
                            <span id="msg-branch_name" class="col-lg-12 error"> </span> 
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <input type="submit" name="submit_form" id="submit_form" class=" btn bg-dark legitRipple" value="Submit">
            </div>
        </div>
    </div>
</div>
</div>

@include('templates.admin.branch.partials.branch-script')
@stop