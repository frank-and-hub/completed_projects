@extends('templates.admin.master')
@section('content')

    <div class="loader" style="display: none;"></div>
    <div class="content">
        <div class="row">
            <div class="col-md-12">
              <!-- Basic layout-->
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <div class="card-body" >
                            <form method="post" action="{!! route('admin.tds_deposit_save') !!}" id="tds_form">
                                 @csrf 
                                 <input type="hidden" name="created_at" class="created_at" id="created_at">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-2">Type<sup>*</sup></label>
                                    <div class="col-lg-4 error-msg">                                   
                                        <select id="type" name="type" class="form-control type">
                                          <option value="">---Please Select Type ---</option>
                                          <option value="1">Interest On Deposite With Pencard</option>
                                          <option value="2">Interest On Deposite Senior Citizen</option>
                                          <option value="3">Interest On Commission With Pencard</option>
                                          <option value="4">Interest On Commission WithOut Pencard</option>
                                          <option value="5">Interest On Deposite Without Pencard</option>
                                        </select>
                                    </div>
                                    <label class="col-form-label col-lg-2">Start Date<sup>*</sup></label>
                                    <div class="col-lg-4 error-msg">
                                        <input type="text" name="start_date" id="start_date" class="form-control" autocomplete="off">
                                    </div>
                                </div>
                               <div class="form-group row">
                                   <label class="col-form-label col-lg-2">Tds % <sup>*</sup></label>
                                    <div class="col-lg-4  error-msg">
                                        <input type="text" id="tds_per" name="tds_per" class="form-control ">
                                    </div>
                                    <label class="col-form-label col-lg-2">Tds Amount<sup>*</sup></label>
                                     <div class="col-lg-4 error-msg">
                                        <input type="text" id="tds_amount" name="tds_amount" class="form-control " >

                                       
                                    </div>
                                </div>
                                 <div class="text-right">
                                    <input type="submit" name="submitform" value="Submit" class="btn btn-primary ">
                                </div>
                            </form>
                        </div>
                    </div>
                    <!-- /basic layout -->
                </div>

            </div>

        </div>

    </div>

@stop



@section('script')

    @include('templates.admin.tds_deposit.partials.script')

@stop