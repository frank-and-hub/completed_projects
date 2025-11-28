@extends('layouts/branch.dashboard')

@section('content')
@section('css')
	<style>
	.table thead th{
		text-transform :none;
	} 
	.table thead th{
		text-transform :none;
	}
    .hideTableData {
        display: none;
    }
	</style> 
@endsection
<div class="container-fluid mt--6">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-lg-12">
                <div class="card bg-white">
                <div class="card-body">
                    <div class="">
                        <h3 class="">Member Listing</h3>
                    </div>
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
                                                <input type="text" class="form-control  " name="start_date" id="start_date" readonly >
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">To Date </label>
                                        <div class="col-lg-12 error-msg">
                                            <div class="input-group">
                                                <input type="text" class="form-control  " name="end_date" id="end_date" readonly >
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @include('templates.GlobalTempletes.role_type',[
                                'dropDown'=> $branchCompany[Auth::user()->branches->id],
                                'name'=>'company_id',
                                'apply_col_md'=>false,
                                'filedTitle' => 'Company'
                                ])
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Customer ID </label>
                                        <div class="col-lg-12 error-msg">
                                            <input type="text" name="customer_id" id="customer_id" class="form-control"  >
                                        </div>
                                    </div>
                                </div>   
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Member Name </label>
                                        <div class="col-lg-12 error-msg">
                                            <input type="text" name="name" id="name" class="form-control"  >
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Member ID </label>
                                        <div class="col-lg-12 error-msg">
                                            <input type="text" name="member_id" id="member_id" class="form-control"  >
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
                                <div class="col-lg-12 text-right" >
                                    <input type="hidden" name="is_search" id="is_search" value="no">
                                    <input type="hidden" name="member_export" id="member_export" value="">
                                    <button type="button" class=" btn btn-primary legitRipple" onClick="searchForm()" >Submit</button>
                                    <button type="button" class="btn btn-gray legitRipple" id="reset_form" onClick="resetForm()" >Reset </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="row table-section hideTableData">  
            <div class="col-lg-12">                

                <div class="card bg-white shadow">
                    <div class="card-header bg-transparent">
                        <div class="row">
                            <div class="col-md-8">
                                <h3 class="mb-0 text-dark">Members</h3>
                            </div>
                            <div class="col-md-4">
                                <div class="text-right">
                                    <button type="button" class="btn btn-primary legitRipple export ml-2" data-extension="0" style="float: right;">Export Excel</button>
                                    
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table id="member_listing" class="table table-flush">
                            <thead class="">
                                <tr>
                                    <th>S/N</th>
                                    <th>Br Name</th> 
                                    <!--<th>SO Name</th>
                                    <th>RO Name</th>
                                    <th>ZO Name</th> -->
                                    <th>Member Name</th>
                                    <th>Member ID</th>
                                    <th>Customer ID</th>
                                    <th>Join Date</th>
                                    <th>DOB</th>
                                    <th>Gender</th>
                                    <!--<th>Account No</th> -->
                                    <th>Mobile</th> 
                                    <th>State</th>
                                    <th>District</th> 
                                    <th>City</th>
                                    <th>Village Name</th> 
                                    <th>Pin Code</th>  
                                    <th>ID Proof</th>                               
                                    <th>Address Proof</th> 
                                    <th>Nominee Name</th>
                                    <th> Age</th>
                                    <th>Relation</th>
                                    <th>Associate Name</th> 
                                    <th>Associate Code</th> 
                                    <th>Status</th>
                                    <th>Image Uploaded</th>
                                    <!--<th>Nominee Gender</th>
                                    <th>Address</th>-->
                                    <th>Action</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
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
            <form action="{{route('correction.request')}}" method="post" id="member-correction-form" name="member-correction-form">
              @csrf
              <input type="hidden" name="correction_type_id" id="correction_type_id" value="">
              <input type="hidden" name="correction_type" id="correction_type" value="0">
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
</div>  --> 
@stop

@section('script')
@include('templates.branch.member_management.partials.listing_script')
@stop