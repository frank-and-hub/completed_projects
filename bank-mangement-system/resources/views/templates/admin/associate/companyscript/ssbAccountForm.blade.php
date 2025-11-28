<!--<div class="col-lg-12 d-none" >-->
    <div class="card bg-white d-none" id="ssbAccountForm">
        <div class="card-body">
            <h3 class="card-title mb-3">SSB Account Form </h3>
            <div class="row">
                <div class="col-lg-6">
                    <div class="form-group row">
                        <label class="col-form-label col-lg-4">Amount<sup class="required">*</sup></label>
                        <div class="col-lg-8 error-msg">
                            <div class="rupee-img"></div>
                            <input type="text" name="ssb_amount" id="ssb_amount" class="form-control rupee-txt" value="500" readonly="readonly">
                            <input type="hidden" name="ssb_accountexists" id="ssb_accountexists" class="form-control rupee-txt" value="0" readonly="readonly">
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="form-group row">
                        <label class="col-form-label col-lg-4">Form No.<sup class="required">*</sup></label>
                        <div class="col-lg-8 error-msg">
                            <input type="text" name="ssb_form_no" id="ssb_form_no" class="form-control" value="">
                        </div>
                    </div>
                </div>
            </div>
            <h4 class="card-title mb-3">First Nominee Detail</h4>
            <div class="row">
                <div class="col-lg-12">
                    <div class="form-group row">
                        <div class="col-lg-9 error-msg">
                            <div class="row">
                                <div class="col-lg-9">
                                    <div class="custom-control custom-checkbox mb-3 ">
                                        <input type="checkbox" id="old_ssb_no_detail" name="old_ssb_no_detail" class="custom-control-input" value="1">
                                        <label class="custom-control-label" for="old_ssb_no_detail" value="1">Yes</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="form-group row">
                        <label class="col-form-label col-lg-4">Full Name<sup class="required">*</sup></label>
                        <div class="col-lg-8 error-msg">
                            <input type="text" name="ssb_first_first_name" id="ssb_first_first_name" class="form-control">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-form-label col-lg-4">Relationship<sup class="required">*</sup></label>
                        <div class="col-lg-8" class="form-control">

                            <select name="ssb_first_relation" id="ssb_first_relation" class="form-control">
                                <option value="">Select Relation</option>
                                @foreach ($relations as $val)
                                <option value="{{ $val->id }}">{{ $val->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-form-label col-lg-4">Date of Birth<sup class="required">*</sup>
                        </label>
                        <div class="col-lg-8 error-msg">
                            <div class="input-group">
                                <span class="input-group-prepend">
                                    <span class="input-group-text"><i class="ni ni-calendar-grid-58"></i></span>
                                </span>
                                <input type="text" class="form-control " name="ssb_first_dob" id="ssb_first_dob">
                            </div>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-form-label col-lg-4">Percentage<sup class="required">*</sup></label>
                        <div class="col-lg-8 error-msg">
                            <input type="text" name="ssb_first_percentage" id="ssb_first_percentage"
                                class="form-control">
                        </div>
                    </div>

                </div>
                <div class="col-lg-6">

                    <div class="form-group row">
                        <label class="col-form-label col-lg-4">Gender<sup class="required">*</sup>
                        </label>
                        <div class="col-lg-8 error-msg">
                            <div class="row">
                                <div class="col-lg-4">
                                    <div class="custom-control custom-radio mb-3 ">
                                        <input type="radio" id="ssb_first_gender_male" name="ssb_first_gender"
                                            class="custom-control-input" value="1">
                                        <label class="custom-control-label" for="ssb_first_gender_male">Male</label>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="custom-control custom-radio mb-3  ">
                                        <input type="radio" id="ssb_first_gender_female" name="ssb_first_gender"
                                            class="custom-control-input" value="0">
                                        <label class="custom-control-label" for="ssb_first_gender_female">Female</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-form-label col-lg-4">Age<sup class="required">*</sup></label>
                        <div class="col-lg-8 error-msg">
                            <input type="text" name="ssb_first_age" id="ssb_first_age" class="form-control"
                                readonly="readonly">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-form-label col-lg-4">Mobile No<sup class="required">*</sup></label>
                        <div class="col-lg-8 error-msg">
                            <input type="text" name="ssb_first_mobile_no" id="ssb_first_mobile_no" class="form-control">
                        </div>
                    </div>

                </div>
                <div class="col-lg-12">
                    <div class="form-group row">
                        <div class="col-lg-12">
                            <input type="hidden" name="ssb_first_first_name_old" id="ssb_first_first_name_old">
                            <input type="hidden" name="ssb_first_relation_old" id="ssb_first_relation_old">
                            <input type="hidden" name="ssb_first_dob_old" id="ssb_first_dob_old">
                            <input type="hidden" name="ssb_first_age_old" id="ssb_first_age_old">
                            <input type="hidden" name="ssb_first_mobile_no_old" id="ssb_first_mobile_no_old">
                            <input type="hidden" name="ssb_first_gender_old" id="ssb_first_gender_old">

                            <button type="button" class="btn btn-primary" id="second_nominee_ssb">Add Nominee
                            </button>
                            <button type="button" class="btn btn-primary" id="second_nominee_ssb_remove"
                                style="display: none">Remove Nominee</i></button>
                        </div>
                    </div>
                </div>
            </div>
            <DIV id="ssb_second_no_div" style="display: none">
                <h4 class="card-title mb-3">Second Nominee Detail</h4>
                <div class="row">
                    <div class="col-lg-6">
                        <div class="form-group row">
                            <label class="col-form-label col-lg-4">Full Name<sup class="required">*</sup></label>
                            <div class="col-lg-8 error-msg">
                                <input type="text" name="ssb_second_first_name" id="ssb_second_first_name"
                                    class="form-control">
                                <input type="hidden" name="ssb_second_validate" id="ssb_second_validate"
                                    class="form-control" value="0">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-form-label col-lg-4">Relationship<sup class="required">*</sup></label>
                            <div class="col-lg-8" class="form-control">

                                <select name="ssb_second_relation" id="ssb_second_relation" class="form-control">
                                    <option value="">Select Relation</option>
                                    @foreach ($relations as $val)
                                    <option value="{{ $val->id }}">{{ $val->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-form-label col-lg-4">Date of Birth<sup class="required">*</sup>
                            </label>
                            <div class="col-lg-8 error-msg">
                                <div class="input-group">
                                    <span class="input-group-prepend">
                                        <span class="input-group-text"><i class="ni ni-calendar-grid-58"></i></span>
                                    </span>
                                    <input type="text" class="form-control " name="ssb_second_dob" id="ssb_second_dob">
                                </div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-form-label col-lg-4">Percentage<sup class="required">*</sup></label>
                            <div class="col-lg-8 error-msg">
                                <input type="text" name="ssb_second_percentage" id="ssb_second_percentage"
                                    class="form-control">
                            </div>
                        </div>

                    </div>
                    <div class="col-lg-6">

                        <div class="form-group row">
                            <label class="col-form-label col-lg-4">Gender<sup class="required">*</sup>
                            </label>
                            <div class="col-lg-8 error-msg">
                                <div class="row">
                                    <div class="col-lg-4">
                                        <div class="custom-control custom-radio mb-3 ">
                                            <input type="radio" id="ssb_second_gender_male" name="ssb_second_gender"
                                                class="custom-control-input" value="1">
                                            <label class="custom-control-label"
                                                for="ssb_second_gender_male">Male</label>
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="custom-control custom-radio mb-3  ">
                                            <input type="radio" id="ssb_second_gender_female" name="ssb_second_gender"
                                                class="custom-control-input" value="0">
                                            <label class="custom-control-label"
                                                for="ssb_second_gender_female">Female</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-form-label col-lg-4">Age<sup class="required">*</sup></label>
                            <div class="col-lg-8 error-msg">
                                <input type="text" name="ssb_second_age" id="ssb_second_age" class="form-control"
                                    readonly="readonly">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-form-label col-lg-4">Mobile No<sup class="required">*</sup></label>
                            <div class="col-lg-8 error-msg">
                                <input type="text" name="ssb_second_mobile_no" id="ssb_second_mobile_no"
                                    class="form-control">
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
<!--</div>-->