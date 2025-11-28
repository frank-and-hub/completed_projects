@extends('templates.admin.master')
@section('content')
@section('css')
<style>
    .hideTableData {
        display: none;
    }
</style>
@endsection
<?php
$date_filter='';
$type_filter='';
$headId = '';
if(isset($_GET['type']))
{
  $type_filter=$_GET['type'];
}
if(isset($_GET['head_id']))
{
  $headId=$_GET['head_id'];
}
$dataid = '';
?>
<style>
    sup{
        color:red;
    }
</style>
  <div class="content"> 
      <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">Search Filter</h6>
                    </div>
                    <div class="card-body">
                        <form action="#" method="post" enctype="multipart/form-data" id="filter" name="filter">
                          <input type="hidden" name="type2" value="{{$type_filter}}" id="type2" >
						  <input type="hidden" name="headId" value="<?php echo $headId; ?>" id="headId" >
                        @csrf
                        <div class="row">
                              @include('templates.GlobalTempletes.role_type',['dropDown'=>$AllCompany,'filedTitle'=>'Company','name'=>'company_id','apply_col_md'=>true])
                              <div class="col-md-6 ">
                                  <div class="form-group row">

                                      <label class="col-form-label ml-5">Type<sup style="color:red">*<sup></label>
                                      <div class="col-lg-8 error-msg ml-5">
                                           <select class="form-control" id="type" name="type">
                                            <option value =''>Select Type </option>
                                           <option value ='15' <?php if($headId == "15"){ ?>selected <?php } ?>>Shareholder </option>
                                           <option value ='19' <?php if($headId == "19"){ ?>selected <?php } ?>>Director</option>
                                        </select>
                                      </div>
                                  </div>
                              </div>
                              <div class="col-md-12">
                                    <div class="form-group row"> 
                                        <div class="col-lg-12 text-right" >
                                            <input type="hidden" name="is_search" id="is_search" value="no">
                                            <input type="hidden" name="fund_transfer_export" id="fund_transfer_export" value="">
                                            <button type="button" class=" btn bg-dark legitRipple" onClick="searchForm ()" >Submit</button>
                                            <button type="button" class="btn btn-gray legitRipple" id="reset_form" onClick="resetForm()" >Reset </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
      <!-- Table -->
            <div class="col-md-12 table-section hideTableData">
                <div class="card">
                    <div class="">
                        <div class="card-header header-elements-inline ">
                            <h6 class="card-title font-weight-semibold">ShareHolder / Director </h6>
                    <div class="">
                        <a class="font-weight-semibold" href="{!! route('admin.shareholder.create') !!}"><i class="icon-file-plus mr-2 "></i>ShareHolder/Director</a>
                    </div>
                        </div>
                        <table id="share_table" class="table datatable-show-all">
                            <thead>
                                <tr>
                                    <th>S/N</th>
                                    <th>Company</th>
                                    <th>Type</th>
                                    <th>Name</th>
                                    <th>Father Name</th>
                                    <th>Address</th>
                                    <th>Pan No.</th>
                                    <th>Aadhaar No.</th>
                                    <th>Firm Name</th>
                                    <th>Email</th>
                                    <th>Contact</th>
                                    <th>Bank Name</th>
                                    <th>Branch Name</th>
                                    <th>Account No.</th>
                                    <th>Current Balance</th>
                                    <th>Ifsc</th>
                                    <th>Member Id</th>
                                    <th>Ssb Account</th>
                                    <th>Remark</th>
                                    <th>Created Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>                    
                        </table>
                    </div>
                </div>
            </div>
        </div>
  </div>
    @include('templates.admin.shareholder.partials.script')
@stop