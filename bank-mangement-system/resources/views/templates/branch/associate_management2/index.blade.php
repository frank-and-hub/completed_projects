@extends('layouts/branch.dashboard')

@section('content')
@section('css')
	<style>
	.table thead th{
		text-transform :none;
	}
	</style>
@endsection
<div class="container-fluid mt--6">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-lg-12">
                <div class="card bg-white">
                <div class="card-body page-title"> 
                        <h3 class="">Associate Listing</h3>
                        <a href="{!! route('branch.associate_list') !!}" style="float:right" class="btn btn-secondary">Back</a>
                    
                </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="card bg-white">
                <div class="card-header header-elements-inline">
                    <h3 class="card-title font-weight-semibold">Search Filter</h3>
                </div>
                <div class="card-body">
                    <form action="#" method="post" enctype="multipart/form-data" id="filter" name="filter">
                        @csrf
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">From Date </label>
                                    <div class="col-lg-12 error-msg">
                                        <div class="input-group">
                                            <input type="text" class="form-control  " name="start_date" id="start_date"  >
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">To Date </label>
                                    <div class="col-lg-12 error-msg">
                                        <div class="input-group">
                                            <input type="text" class="form-control  " name="end_date" id="end_date"  >
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Associate Name </label>
                                    <div class="col-lg-12  error-msg">
                                        <input type="text" name="name" id="name" class="form-control"  >
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Associate code  </label>
                                    <div class="col-lg-12 error-msg">
                                        <input type="text" name="associate_code" id="associate_code" class="form-control"  >
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Senior Code    </label>
                                    <div class="col-lg-12 error-msg">
                                        <input type="text" name="sassociate_code" id="sassociate_code" class="form-control"  >
                                    </div>
                                </div>
                            </div>
                           <!--  <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Achieved Target </label>
                                    <div class="col-lg-12  error-msg">
                                        <select class="form-control" id="achieved" name="achieved">
                                            <option value="">Select Achieved Target</option>
                                            <option value="1">Achieved</option>
                                            <option value="0">Not Achieved</option>
                                        </select>
                                    </div>
                                </div>
                            </div> -->
                            <div class="col-md-12">
                                <div class="form-group row">
                                    <div class="col-lg-12 text-right" >
                                        <input type="hidden" name="is_search" id="is_search" value="no">
                                        <input type="hidden" name="member_export" id="member_export" value="">
                                        <button type="button" class=" btn btn-primary legitRipple" onClick="searchForm()" >Submit</button>
                                        <button type="button" class="btn btn-gray legitRipple" id="reset_form" onClick="resetForm()" >Reset </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            </div>
        </div>
        <div class="row">  
            <div class="col-lg-12">                

                <div class="card bg-white shadow">
                    <div class="card-header bg-transparent">
                        <div class="row">
                            <div class="col-md-8">
                                <h3 class="mb-0 text-dark">Associates</h3>
                            </div>
                            <div class="col-md-4 text-right">
                                <button type="button" class="btn btn-primary legitRipple export ml-2" data-extension="0" style="float: right;">Export xslx</button>
                                <button type="button" class="btn btn-primary legitRipple export" data-extension="1">Export PDF</button>
                            </div>
                            </div>
                        </div>
                    
                    <div class="table-responsive">
                        <table id="associate_listing" class="table table-flush">
                            <thead class="">
                              <tr>
                                <th>S/N</th>
                               
                                <th>Branch Name</th>
                                 <th>Branch Code</th>
								 <th>Associate  Name</th>
                                <!--<th>SO Name</th>
                                <th>RO Name</th>
                                <th>ZO Name</th> -->
                                <th>Member ID</th>
                                <th>Associate ID</th>
								 <th>Joining Date(as associate)</th>
                                
                                <th>Associate DOB</th>
                                <th>Nominee Name</th>
									<th>Relation</th>
									<th>Nominee Age</th>
                                <th>Email ID</th>
                                <th>Mobile No</th>
                                <!--<th>Senior Code</th>
								<th>Senior Name</th>-->
								
                                <th>Status</th>
                                <th>Is Uploaded</th>
<!--                                 <th>Achieved Target</th>
 -->                                   <th>Address</th>
                                    <th>State</th>
                                    <th>District</th> 
                                    <th>City</th>
                                    <th>Village Name</th> 
                                    <th>Pin Code</th>  
                                    <th>First ID Proof</th>                               
                                    <th>Second ID Proof</th> 
                                <th>Action</th>
                              </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
</div>

<!-- <div class="modal fade" id="correction-form" tabindex="-1" role="dialog" aria-labelledby="modal-form" aria-hidden="true">
  <div class="modal-dialog modal- modal-dialog-centered modal-sm" role="document" style="max-width: 600px !important; ">
    <div class="modal-content">
      <div class="modal-body p-0">
        <div class="card bg-white border-0 mb-0">
          <div class="card-header bg-transparent pb-2ß">
            <div class="text-dark text-center mt-2 mb-3">Correction Request</div>
          </div>
          <div class="card-body px-lg-5 py-lg-5">
            <form action="{{route('correction.request')}}" method="post" id="associate-correction-form" name="associate-correction-form">
              @csrf
              <input type="hidden" name="correction_type_id" id="correction_type_id" value="">
              <input type="hidden" name="correction_type" id="correction_type" value="1">
              <div class="form-group row">
                <div class="col-lg-12">
                  <textarea name="corrections" name="corrections" rows="6" cols="50" class="form-control" placeholder="Corrections"></textarea>
                </div>
              </div>  

              <div class="text-right">
                <input type="submit" name="submitform" value="Submit" class="btn btn-primary">
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div> -->
@stop

@section('script')
@include('templates.branch.associate_management.partials.listing_script')
@stop