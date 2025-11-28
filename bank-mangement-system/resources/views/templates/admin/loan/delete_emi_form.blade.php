@extends('templates.admin.master')

@section('content')
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                       <div class="card-header header-elements-inline">
                    <h6 class="card-title font-weight-semibold">Search Filter</h6>
                </div>
                <div class="card-body">
                    <form action="#" method="post" enctype="multipart/form-data" id="filter" name="filter">
                    @csrf
                    <input type="hidden" name="globaldate" id="gloDate" class="create_application_date" >
                      <div class="row">
                          <div class="col-md-4">
                              <div class="form-group row">
                                  <label class="col-form-label col-lg-12"> Loan Account Number </label>
                                  <div class="col-lg-12 error-msg">
                                       <div class="input-group">
                                           <input type="text" class="form-control  " name="account_number" id="account_number" required=""> 
                                         </div>
                                  </div>
                              </div>
                          </div>
                         
                          <div class="col-md-12">
                                <div class="form-group row"> 
                                    <div class="col-lg-12 text-right" >
                                        <input type="hidden" name="is_search" id="is_search" value="no">
                                        <button type="submit" class=" btn bg-dark legitRipple" onClick="searchForm()" >Submit</button>
                                        <button type="button" class="btn btn-gray legitRipple" id="reset_form" onClick="resetForm()" >Reset </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                    <div id="filter_data">
                    </div>    
                    </div>
                </div>
            </div>
        </div>
    </div>

@stop

@section('script')

<script src="{{url('/')}}/asset/js/sweetalert.min.js"></script>

@include('templates.admin.loan.partials.delete_emi_script')

@endsection