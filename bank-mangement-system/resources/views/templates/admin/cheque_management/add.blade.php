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
          <h4 class="card-title mb-3">Add Cheque</h4>
        </div>
        <div class="card-body">
          <form action="{!! route('admin.cheque_save') !!}" method="post" enctype="multipart/form-data" id="cheque_add" name="cheque_add"  >
            @csrf
            <div class="row">
              <div class="col-lg-12"> 
                <div class="row">
                  @include('templates.GlobalTempletes.role_type',[
                    'dropDown'=> $company,
                    'name'=>'company_id',
                    'apply_col_md'=>false,
                                  'filedTitle' => 'Company'
                    ])
                  <div class="col-lg-6"> 
                    <div class="form-group row">
                      <label class="col-form-label col-lg-3">Bank Name<sup class="required">*</sup></label>
                      <div class="col-lg-9 error-msg">
                        <select   name="bank_id" id="bank_id" class="form-control" >  <option value="">Select Bank Name</option>
                              {{-- @foreach ($bank as $val)
                                  <option value="{{ $val->id }}">{{ $val->bank_name }}</option>
                               @endforeach --}}
                        </select>

                        <input type="hidden" class="form-control created_at " name="created_at" id="created_at"  >
                         <input type="hidden" class="form-control create_application_date " name="create_application_date" id="create_application_date"  >
                                            
                      </div>
                    </div>
                  </div> 
                  <div class="col-lg-6"> 
                    <div class="form-group row">
                      <label class="col-form-label col-lg-4">Account Number<sup class="required">*</sup></label>
                      <div class="col-lg-8 error-msg">
                        <select   name="account_id" id="account_id" class="form-control" >  <option value="">Select Account Number</option> 
                        </select>
                      </div>
                    </div>
                  </div>
                  <div class="col-lg-6"> 
                    <div class="form-group row">
                      <label class="col-form-label col-lg-3">Date<sup class="required">*</sup></label>
                      <div class="col-lg-9 error-msg">
                        <div class="input-group">
                            <span class="input-group-prepend mr-0 ">
                              <span class="input-group-text"><i class="ni ni-calendar-grid-58"></i></span>
                            </span>
                             <input type="text" class="form-control create_application_date " name="cheque_date" id="cheque_date"  readonly >
                             <input type="hidden" class="form-control created_at " name="created_at" id="created_at"  readonly >
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-lg-12">                 
                <div class="row">                  
                  <div class="col-lg-6"> 
                    <div class="form-group row">
                      <label class="col-form-label col-lg-4">From <sup class="required">*</sup></label>
                      <div class="col-lg-8 error-msg">
                        <input type="text" name="cheque_from" id="cheque_from" class="form-control"  >
                      </div>
                    </div>
                  </div> 
                  <div class="col-lg-6"> 
                    <div class="form-group row">
                      <label class="col-form-label col-lg-3">To Cheque Number<sup class="required">*</sup></label>
                      <div class="col-lg-9 error-msg">
                        <input type="text" name="cheque_to" id="cheque_to" class="form-control"  >
                      </div>
                    </div>
                  </div> 
                  <div class="col-lg-6"> 
                    <div class="form-group row">
                      <label class="col-form-label col-lg-4">Total Cheque<sup class="required">*</sup></label>
                      <div class="col-lg-8 error-msg">
                        <input type="text" name="total_cheque" id="total_cheque" class="form-control"  readonly>
                      </div>
                    </div>
                  </div> 

                </div>
              </div>

              <div class="col-lg-12">
                <div class="form-group row text-center"> 
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
@include('templates.admin.cheque_management.partials.script')
@stop