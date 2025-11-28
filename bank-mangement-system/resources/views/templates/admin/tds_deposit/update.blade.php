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
                            <form method="post" action="{!! route('admin.tds_deposit_update') !!}" id="tds_form">
                                 @csrf 
                                
                                 <input type="hidden" name="created_at" class="created_at" id="created_at">
                                  <div class="form-group row">
                                    <label class="col-form-label col-lg-3">Bank Name</label>
                                    <div class="col-lg-9 error-msg">
                                      <input type="text" name="bank_name" id="bank_name" class=" form-control"  >
                                      <input type="hidden" name="bank_id" id="bank_id" class=" form-control"  value="0"  >
                                    </div>
                                  </div>
                                 <div class="text-right">
                                    <input type="submit" name="submitform" value="Update" class="btn btn-primary ">
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