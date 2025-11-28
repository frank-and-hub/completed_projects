<!-- <div class="col-lg-12 " > -->
{!! Form::hidden('created_at',null,['class'=>'created_at']) !!}
{!! Form::hidden('id',null,['id'=>'id','class'=>'form-control']) !!}
{!! Form::hidden('ssb_account',old('ssb_account'),['id'=>'ssb_account']) !!}
{!! Form::hidden('rd_account',old('rd_account'),['id'=>'rd_account']) !!}
{!! Form::hidden('ssb_account_number',old('ssb_account_number'),['id'=>'ssb_account_number']) !!}  
{!! Form::hidden('ssb_account_name',old('ssb_account_name'),['id'=>'ssb_account_name']) !!}  
{!! Form::hidden('ssb_account_amount',old('ssb_account_amount'),['id'=>'ssb_account_amount']) !!}
{!! Form::hidden('rd_account_number',old('rd_account_number'),['id'=>'rd_account_number']) !!}  
{!! Form::hidden('rd_account_name',old('rd_account_name'),['id'=>'rd_account_name']) !!}  
{!! Form::hidden('rd_account_amount',old('rd_account_amount'),['id'=>'rd_account_amount']) !!}  
{!! Form::hidden('associate',old('associate'),['id'=>'associate']) !!}
    <div class="card bg-white d-none" id="associateFormInformation">
        <div class="card-body">
            <h3 class="card-title mb-3">Associate Form Information</h3>
            <div class="row">
                <div class="col-lg-6">
                    <div class="form-group row">
                        <label class="col-form-label col-lg-4">Form No<sup class="required">*</sup></label>
                        <div class="col-lg-8 error-msg">
                            <input type="text" name="form_no" id="form_no" class="form-control">
                        </div>
                    </div>
                </div>
                        {!! Form::hidden('roi',null,['id'=>'roi']) !!}
                <div class="col-lg-6">
                    <div class="form-group row">
                        <label class="col-form-label col-lg-4">Application Date<sup class="required">*</sup></label>
                        <div class="col-lg-8 error-msg">
                            <div class="input-group">
                                <span class="input-group-prepend">
                                    <span class="input-group-text"><i class="ni ni-calendar-grid-58"></i></span>
                                </span>
                                <input type="text" class="form-control create_application_date" name="application_date" id="application_date" readonly value="{{ date('d/m/Y') }}">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="form-group  row">
                        <label class="col-form-label col-lg-4">Senior Code</label>
                        <div class="col-lg-8 error-msg">
                            <input type="text" name="senior_code" id="senior_code" class="form-control">
                            <input type="hidden" name="senior_id" id="senior_id" class="form-control">
                            <span class="error invalid-feedback" id="associate_msg"></span>
                        </div>
                    </div>
                </div>
                {!!  Form::hidden('current_carder2',null,['id'=>'current_carder2'])!!}
                {!!  Form::hidden('recipt_id',null,['id'=>'recipt_id'])!!}
                {!!  Form::hidden('ssb_form_no_form',null,['id'=>'ssb_form_no_form'])!!}
                <div class="col-lg-6">
                    <div class=" row  form-group ">
                        <label class=" col-lg-4">Name</label>
                        <div class="col-lg-8 ">
                            <input type="text" name="senior_name" id="senior_name" class="form-control" readonly>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="row form-group  ">
                        <label class=" col-lg-4">Mobile No</label>
                        <div class="col-lg-8 " id="">
                            <input type="text" name="senior_mobile_no" id="senior_mobile_no" class="form-control" readonly>
                            <input type="hidden" name="seniorcarder_id" id="seniorcarder_id" class="form-control">
                            <span class="error invalid-feedback"></span>
                        </div>
                    </div>
                </div>


                <div class="col-lg-6">
                    <div class="form-group row">
                        <label class="col-form-label col-lg-4">Assign Carder<sup
                                class="required">*</sup></label>
                        <div class="col-lg-8 error-msg">
                            <select class="form-control select" name="current_carder" id="current_carder">
                                <option value="">Select Carder</option>
                                {{-- @foreach( $carder as $val )
                                <option value="{{ $val->id }}">{{ $val->name }}({{ $val->short_name }})
                                </option> @endforeach
                                --}}
                            </select>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
<!-- </div> -->