<!--<div class="col-lg-12 d-none">-->
    <div class="card bg-white" id="associateDependents">
        <div class="card-body">
            <h3 class="card-title mb-3">Details of Associate's dependents </h3>
            <div class="row">
                <div class="col-lg-12" id="add_dependent">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="form-group row">
                                <div class="col-lg-12">

                                    <button type="button" class="btn btn-primary" id="btnAdd">Add More
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="form-group row">
                                <label class="col-form-label col-lg-4">Full Name</label>
                                <div class="col-lg-8 error-msg">
                                    <input type="text" name="dep_first_name" id="dep_first_name" class="form-control">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-form-label col-lg-4">Age</label>
                                <div class="col-lg-8 error-msg">
                                    <input type="text" name="dep_age" id="dep_age" class="form-control">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-form-label col-lg-4">Relation</label>
                                <div class="col-lg-8 error-msg">
                                    <select name="dep_relation" id="dep_relation" class="form-control">
                                        <option value="">Select Relation</option>
                                        @foreach ($relations as $val)
                                        <option value="{{ $val->id }}">{{ $val->name }}</option>
                                        @endforeach
                                    </select>

                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-form-label col-lg-4">Per month income</label>
                                <div class="col-lg-8 error-msg">
                                    <input type="text" name="dep_income" id="dep_income" class="form-control">
                                </div>
                            </div>

                        </div>
                        <div class="col-lg-6">
                            <div class="form-group row">
                                <label class="col-form-label col-lg-4">Gender</label>
                                <div class="col-lg-8 error-msg">
                                    <div class="row">
                                        <div class="col-lg-4">
                                            <div class="custom-control custom-radio mb-3 ">
                                                <input type="radio" id="dep_gender_male" name="dep_gender" class="custom-control-input" value="1">
                                                <label class="custom-control-label" for="dep_gender_male">Male</label>
                                            </div>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="custom-control custom-radio mb-3  ">
                                                <input type="radio" id="dep_gender_female" name="dep_gender" class="custom-control-input" value="0" checked="checked">
                                                <label class="custom-control-label" for="dep_gender_female">Female</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-form-label col-lg-4">Marital status</label>
                                <div class="col-lg-8 error-msg">
                                    <div class="row">
                                        <div class="col-lg-4">
                                            <div class="custom-control custom-radio mb-3 ">
                                                <input type="radio" id="dep_married" name="dep_marital_status" class="custom-control-input" value="1">
                                                <label class="custom-control-label" for="dep_married">Married</label>
                                            </div>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="custom-control custom-radio mb-3  ">
                                                <input type="radio" id="dep_unmarried" name="dep_marital_status" class="custom-control-input" value="0" checked="checked">
                                                <label class="custom-control-label" for="dep_unmarried">Un Married</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-form-label col-lg-4">Living with Associate</label>
                                <div class="col-lg-8 error-msg">
                                    <div class="row">
                                        <div class="col-lg-4">
                                            <div class="custom-control custom-radio mb-3 ">
                                                <input type="radio" id="dep_living_yes" name="dep_living" class="custom-control-input" value="1">
                                                <label class="custom-control-label" for="dep_living_yes">Yes</label>
                                            </div>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="custom-control custom-radio mb-3  ">
                                                <input type="radio" id="dep_living_no" name="dep_living" class="custom-control-input" value="0" checked="checked">
                                                <label class="custom-control-label" for="dep_living_no">No</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-form-label col-lg-4">Dependent Type</label>
                                <div class="col-lg-8 error-msg">
                                    <div class="row">
                                        <div class="col-lg-4">
                                            <div class="custom-control custom-radio mb-3 ">
                                                <input type="radio" id="dep_type_fully" name="dep_type" class="custom-control-input" value="1">
                                                <label class="custom-control-label" for="dep_type_fully">Fully</label>
                                            </div>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="custom-control custom-radio mb-3  ">
                                                <input type="radio" id="dep_type_partially" name="dep_type" class="custom-control-input" value="0" checked="checked">
                                                <label class="custom-control-label" for="dep_type_partially">Partially</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-12">
                            <hr>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<!--</div>-->