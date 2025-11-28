@extends('templates.admin.master')

@section('content')
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">Branch Password Change</h6>
                    </div>
                    <form action="{{route('admin.branchChangedPassword')}}" method="post" id="branchChangePassword">
                        @csrf
                        <div class="modal-body">

                            <div class="form-group row">
                                <div class="col-lg-6">
                                    <input type="hidden" name="branch" value="{{ $branchId }}">
                                    <label class="col-form-label col-lg-12"> New Password<sup>*</sup></label>
                                    <input type="password" name="password" id="password" class="form-control" autocomplete="off" required>
                                </div>
                                <div class="col-lg-6">
                                    <label class="col-form-label col-lg-12">New Confirm Password<sup>*</sup></label>
                                    <input type="password" name="password_confirmation" class="form-control" required>
                                </div>
                        </div>
                        </div>
                        <div class="modal-footer">
                            <a href="{{ url()->previous() }}" type="button" class="btn btn-link" data-dismiss="modal">Back</a>
                            <button type="submit" class="btn bg-dark" onClick="validatePassword();">Change Password<i class="icon-paperplane ml-2"></i></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@include('templates.admin.branch.partials.branch-edit-script')
@stop
