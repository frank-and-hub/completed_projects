<!-- <div class="col-lg-12 " > -->
    <div class="card bg-white d-none" id="associateFormInformation">
        <div class="card-body">
        {!! Form::hidden('roi',null,['id'=>'roi']) !!}
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
                <div class="col-lg-6">
                    <div class="form-group row">
                        <label class="col-form-label col-lg-4">Application Date<sup class="required">*</sup></label>
                        <div class="col-lg-8 error-msg">
                            <div class="input-group">
                                <span class="input-group-prepend">
                                    <span class="input-group-text"><i class="ni ni-calendar-grid-58"></i></span>
                                </span>
                                @php
                                $stateid = getBranchStateByManagerId(Auth::user()->id);
                                @endphp
                                <input type="text" class="form-control  " name="application_date" id="application_date" readonly value="{{ headerMonthAvailability(date('d'),date('m'),date('Y'),$stateid) }}">
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
                {!!  Form::hidden('receipt_id',null,['id'=>'receipt_id'])!!}
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
                        <label class="col-form-label col-lg-4">Assign Cader<sup
                                class="required">*</sup></label>
                        <div class="col-lg-8 error-msg">
                            <select class="form-control select" name="current_carder" id="current_carder">
                                <option value="">Select Cader</option>
                            </select>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
<!-- </div> -->