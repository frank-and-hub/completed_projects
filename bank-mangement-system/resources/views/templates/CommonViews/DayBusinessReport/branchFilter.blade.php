<?php
$getBranchId = getUserBranchId(Auth::user()->id);
$branch_id = $getBranchId->id;
?>
<div class="container-fluid ">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h3 class="card-title font-weight-semibold">Search Filter</h3>
                    </div>
                    <div class="card-body">
                        <form action="#" method="post" enctype="multipart/form-data" id="filter" name="filter">
                            @csrf
                            <input type="hidden" name="branch" id="branch" value="{{$branch_id}}">
                            <input type="hidden" name="gdatetime" id="gdatetime" class="form-control  gdatetime" value="">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Company Name<sup>*</sup> </label>
                                        <div class="col-lg-12 error-msg">

                                            <select name="company_id" id="company_id" class="form-control">
                                                <option value="">----Please Select Company----</option>
                                                @foreach( $company as $key => $name)
                                                <option value="{{ $key }}">{{ $name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">From Date<sup>*</sup> </label>
                                        <div class="col-lg-12 error-msg">
                                            <input type="text" class="form-control" readonly name="start_date" id="start_date" value="{{ date('d/m/Y') }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">To Date<sup>*</sup> </label>
                                        <div class="col-lg-12 error-msg">
                                            <input type="text" class="form-control  " readonly name="end_date" id="end_date" value="{{ date('d/m/Y') }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group text-right">
                                        <div class="col-lg-12 page">
                                            <input type="hidden" name="is_search" id="is_search" value="no">
                                            <button type="button" class=" btn btn-primary legitRipple" onClick="searchFormBranch()">Submit</button>
                                            <button type="button" class="btn btn-gray legitRipple" id="reset_form" onClick="resetForm()">Reset </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="col-md-12">
    <div id="report_data"></div>
</div>
