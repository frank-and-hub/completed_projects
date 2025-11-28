@extends('templates.admin.master')

@section('content')

<div class="content"> 
  <div class="row"> 
        @if ($errors->any())
            <div class="col-md-12">
                  <div class="alert alert-danger">
                      <ul>
                          @foreach ($errors->all() as $error)
                              <li>{{ $error }}</li>
                          @endforeach
                      </ul>
                  </div>
            </div>
        @endif

    <div class="col-md-12">
      <div class="card">
        <div class="card-header header-elements-inline">
          <h4 class="card-title mb-3">Add Vendor Category</h4>
        </div>
        <div class="card-body">
          <form action="{!! route('admin.vendor.category.save') !!}" method="post" enctype="multipart/form-data" id="category" name="category"  >
            @csrf
            <div class="row">
              <div class="col-lg-12"> 
                <div class="row">
                  <div class="col-lg-6"> 
                    <div class="form-group row">
                      <label class="col-form-label col-lg-4">Name <sup class="required">*</sup></label>
                      <div class="col-lg-8 error-msg">
                        <input type="text" name="name" id="name" class="form-control"  >
                        <input type="hidden" class="form-control created_at " name="created_at" id="created_at"  >
                        <input type="hidden" class="form-control create_application_date " name="create_application_date" id="create_application_date"  >
                      </div>
                    </div>
                  </div>
                  <div class="col-lg-6"> 
                    <div class="form-group row">
                      <label class="col-form-label col-lg-4">Status<sup class="required">*</sup></label>
                      <div class="col-lg-8 error-msg">
                        <select   name="status" id="status" class="form-control" >  
                          <option value="" >Select Status</option> 
                          <option  value="1"     >Active</option> 
                          <option value="0"  >Inactive</option> 

                        </select>
                      </div>
                    </div>
                  </div> 
                                
                  
                   

                </div>
              </div>


              <div class="col-lg-12">
                <div class="form-group row text-right"> 
                  <div class="col-lg-12 ">
                    <button type="submit" class="btn btn-primary">Submit</button>
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
@include('templates.admin.vendor_management.category.partials.script_add')
@stop