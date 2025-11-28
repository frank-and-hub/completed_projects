@extends('templates.admin.master')

@section('content')

    <div class="loader" style="display: none;"></div>

    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <!-- Basic layout-->
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <div class="card-body">
                            <form name="createnewhead" id="head" class="createnewhead" enctype="multipart/form-data">
                                @csrf
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-2">Select Company <sup>*</sup></label>
                                    <div class="col-lg-4 error-msg">
                                        <select name="company" id="company" class="form-control company" autocomplete="off">
                                            <option value="">---Select Company--- </option>
                                            @foreach ($company as $key => $companies)
                                                <option value="{{ $key }}">{{ $companies }}</option>
                                            @endforeach

                                        </select>
                                    </div>

                                </div>
								<input type="hidden" class="create_application_date" name="create_application_date" id="create_application_date">


                                <div class="form-group row">
                                    <label class="col-form-label col-lg-2">Select Head1 <sup>*</sup></label>
                                    <div class="col-lg-4 error-msg">
                                        <select name="head1" id="head1" class="form-control">
                                            <option value="">---Select Parent Head--- </option>
                                        </select>
                                    </div>
                                    <label class="col-form-label col-lg-2">Select Head2</label>
                                    <div class="col-lg-4 error-msg">
                                        <select name="head2" id="head2" class="form-control">
                                            <option value="">---Select Child Subhead--- </option>

                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-2">Select Head 3</label>
                                    <div class="col-lg-4 error-msg">
                                        <select name="head3" id="head3" class="form-control">
                                            <option value="">---Select Child Subhead--- </option>
                                        </select>
                                    </div>
                                    <label class="col-form-label col-lg-2">Select Head 4</label>
                                    <div class="col-lg-4 error-msg">
                                        <select name="head4" id="head4" class="form-control">
                                            <option value="">---Select Child Subhead--- </option>
                                        </select>
                                    </div>

                                </div>
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-2">Title<sup>*</sup></label>
                                    <div class="col-lg-4 error-msg">
                                        <input type="text" name="new_head" id="new_head" class="form-control" autocomplete="off">
                                        <span id="title-error-msg" class="text-danger"></span>
                                    </div>
                                </div>
                                <div class="row" id="headDetails" style="display: none;">
                                    <div class="col-md-12">

                                        <center>
                                            <h4>This head already created under below heads.</h4>
                                            
                                        </center>
                                        <div class="compies_head" id="compies_head">
                                            <div class="card-body">
                                                <h6>This head already created in following companies</h6> 

                                            </div>
                                        </div>
                                        <div class="col-lg-4 error-msg" id="createHead">
                                            <input type="checkbox" name="new_head" id="createHeads" class="">
                                            Do You still wnat to create. In selected Head ?
                                            <span id="title-error-msg" class="text-danger"></span>
                                        </div>
                                        <div class="card-header header-elements-inline">

                                            <div class="card-body">


                                            </div>

                                        </div>
                                        <input type="hidden" name="company_id[]" id="company_id_input">
                                        <input type="hidden" name=" " id="headId">
                                        
                                        <h5 style="display: none" id="text_companies" style="color: red">This head already assigned in all companies</h5>
                                        <div class="col-lg-4 error-msg">
                                            <select class="form-control" id="companies" name="selected_companies[]" multiple>
                                                <option value="">---Select Company---</option>
                                                @foreach ($company as $key => $companies)
                                                    <option value="{{ $key }}">
                                                        {{ $companies }}
                                                    </option>
                                                @endforeach
                                                
                                            </select>
                                        </div>
                                        <div id="error-message" class="text-danger"></div>
                                        <div class="text-right" style="padding-bottom: 30px;">
                                            <button type="button" id="assign" style="display: none;" class="btn btn-primary">Assign</button>
                                        </div>
                                    </div>
                                </div>

                                <div class="text-right">
                                    <button type="button" id="submit" value="Submit" style="display: none"
                                        class="btn btn-primary submit createnewheads">Submit</button>

                                </div>
                            </form>
                        </div>
                    </div>

                </div>
            </div>
        </div>


    </div>
@stop

@section('script')
    @include('templates.admin.account_head_report.head.partials.script')
@stop
