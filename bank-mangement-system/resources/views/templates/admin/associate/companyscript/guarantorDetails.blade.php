<!--<div class="col-lg-12 d-none">-->
    <div class="card bg-white"  id="guarantorDetails">
        <div class="card-body">
            <h3 class="card-title mb-3">Guarantor Details </h3>
            <h5 class="card-title mb-3">1<sup>st</sup>Guarantor Detail </h5>
            <div class="row">
                <div class="col-lg-6">
                    <div class="form-group row">
                        <label class="col-form-label col-lg-4">Full Name<sup class="required">*</sup></label>
                        <div class="col-lg-8 error-msg">
                            <input type="text" name="first_g_first_name" id="first_g_first_name" class="form-control" readonly>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-form-label col-lg-4">Mobile No<sup class="required">*</sup></label>
                        <div class="col-lg-8 error-msg">
                            <input type="text" name="first_g_Mobile_no" id="first_g_Mobile_no" class="form-control" readonly>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="form-group row">
                        <label class="col-form-label col-lg-4">Address<sup class="required">*</sup></label>
                        <div class="col-lg-8 error-msg">
                            <textarea id="first_g_address" name="first_g_address" class="form-control" readonly></textarea>
                        </div>
                    </div>
                </div>
            </div>
            <h5 class="card-title mb-3">2<sup>nd</sup>Guarantor Detail </h5>
            <div class="row">
                <div class="col-lg-6">
                    <div class="form-group row">
                        <label class="col-form-label col-lg-4">Full Name</label>
                        <div class="col-lg-8 error-msg">
                            <input type="text" name="second_g_first_name" id="second_g_first_name" class="form-control">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-form-label col-lg-4">Mobile No</label>
                        <div class="col-lg-8 error-msg">
                            <input type="text" name="second_g_Mobile_no" id="second_g_Mobile_no" class="form-control">
                        </div>
                    </div>
                </div>
                {!! Form::hidden('customerRegisterId',null,['id'=>'customerRegisterId']) !!}
                {!! Form::hidden('ssb_account_number_form',null,['id'=>'ssb_account_number_form']) !!}
                {!! Form::hidden('ssb_account_name_form',null,['id'=>'ssb_account_name_form']) !!}
                {!! Form::hidden('created_at',Session()->get('created_at'),['class'=>'created_at']) !!}
                {!! Form::hidden('ssb_first_first_name2',null,['id'=>'ssb_first_first_name2']) !!}
                {!! Form::hidden('ssb_first_relation2',null,['id'=>'ssb_first_relation2']) !!}
                {!! Form::hidden('ssb_first_gender2',null,['id'=>'ssb_first_gender2']) !!}
                {!! Form::hidden('ssb_first_dob2',null,['id'=>'ssb_first_dob2']) !!}
                {!! Form::hidden('ssb_first_age2',null,['id'=>'ssb_first_age2']) !!}
                {!! Form::hidden('ssb_first_percentage2',null,['id'=>'ssb_first_percentage2']) !!}
                {!! Form::hidden('ssb_first_mobile_no2',null,['id'=>'ssb_first_mobile_no2']) !!}
                {!! Form::hidden('ssb_second_first_name2',null,['id'=>'ssb_second_first_name2']) !!}
                {!! Form::hidden('ssb_second_relation2',null,['id'=>'ssb_second_relation2']) !!}
                {!! Form::hidden('ssb_second_gender2',null,['id'=>'ssb_second_gender2']) !!}
                {!! Form::hidden('ssb_second_dob2',null,['id'=>'ssb_second_dob2']) !!}
                {!! Form::hidden('ssb_second_age2',null,['id'=>'ssb_second_age2']) !!}
                {!! Form::hidden('ssb_second_percentage2',null,['id'=>'ssb_second_percentage2']) !!}
                {!! Form::hidden('ssb_second_mobile_no2',null,['id'=>'ssb_second_mobile_no2']) !!}
                {!! Form::hidden('ssb_second_validate2',null,['id'=>'ssb_second_validate2']) !!}
                <div class="col-lg-6">
                    <div class="form-group row">
                        <label class="col-form-label col-lg-4">Address</label>
                        <div class="col-lg-8 error-msg">
                            <textarea id="second_g_address" name="second_g_address" class="form-control"></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<!--</div>-->