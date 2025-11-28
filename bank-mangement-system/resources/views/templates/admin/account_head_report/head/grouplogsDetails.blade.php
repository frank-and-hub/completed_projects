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

                <div class="card bg-white">


                    <div class="card-body">
                        <h3>Details</h3>
                     
                        <div class="form-group row">
                            <label class="col-form-label col-lg-3">Head Name </label>
                            <div class="col-lg-3 error-msg">
                                <input type="text" name="username" id="username" class=" form-control"
                                    value="{{$details['sub_head']}}" readonly>
                            </div>
                            <label class="col-form-label col-lg-3">Companies </label>
                            <div class="col-lg-3 error-msg">
                                @foreach ($shortNames as $companyNames)
                                {{ $companyNames}},
                                    
                                @endforeach
                                <input type="text" name="employee_code" id="employee_code" class="form-control"
                                    value="" readonly>
                                  
                            </div>
                        </div>
                        
                        <div class="form-group row">
                            <label class="col-form-label col-lg-3">Label </label>
                            <div class="col-lg-3 error-msg">
                                <input type="text" name="employee_name" id="employee_name" class="form-control"
                                    value="{{$newDetails['labels']}}" readonly>
                            </div>
                            <label class="col-form-label col-lg-3">Created By </label>
                            <div class="col-lg-3 error-msg">
                                <input type="text" name="employee_name" id="employee_name" class="form-control"
                                    value="{{$headDetails['created_by']}}" readonly>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-form-label col-lg-3">Descreption</label>
                            <div class="col-lg-6 error-msg">
                                <input type="text" class="form-control"
                                    value="{{$headDetails['description']}}" readonly>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-form-label col-lg-3">Created At</label>
                            <div class="col-lg-9 error-msg">
                                <input type="text"  class="form-control"
                                    value="{{ date("d/m/Y", strtotime(convertDate($headDetails['created_at'])))}}" readonly>
                            </div>
                        </div>

                       

                        

                    </div>
                </div>

            </div>

            <div class="col-md-6">
                <div class="card bg-white">

                    <input type="hidden" name="created_at" class="created_at">
                    <div class="card-body">
                        <h3>Old Details</h3>
                        @php
                            // dd($details['sub_head']);
                        @endphp
                        <div class="form-group row">
                            <label class="col-form-label col-lg-3">Head Name </label>
                            <div class="col-lg-9 error-msg">
                                <input type="text" name="username" id="username" class=" form-control"
                                    value="{{$details['sub_head']}}" readonly>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-form-label col-lg-3">Parent Id </label>
                            <div class="col-lg-9 error-msg">
                                <input type="text" name="employee_code" id="employee_code" class="form-control"
                                    value="{{$parentHeadName->sub_head}}" readonly>
                            </div>
                            
                        </div>
                        <div class="form-group row">
                            <label class="col-form-label col-lg-3">Label </label>
                            <div class="col-lg-9 error-msg">
                                <input type="text" name="employee_name" id="employee_name" class="form-control"
                                    value="{{$details['labels']}}" readonly>
                            </div>
                        </div>

                        {{-- <div class="form-group row">
                            <label class="col-form-label col-lg-3">Previous Parent Id</label>
                            <div class="col-lg-9 error-msg">
                                <input type="text" class="form-control"
                                    value="{{$details['previous_parent_id']}}" readonly>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-form-label col-lg-3">Parent Auto Id</label>
                            <div class="col-lg-9 error-msg">
                                <input type="text"  class="form-control"
                                    value="{{$details['parentId_auto_id']}}" readonly>
                            </div>
                        </div> --}}

                        <div class="form-group row">
                            <label class="col-form-label col-lg-3">CR Nature </label>
                            <div class="col-lg-9 error-msg">
                                <input type="text"  class="form-control"
                                    value="{{$details['cr_nature']}}" readonly>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-form-label col-lg-3">DR Nature </label>
                            <div class="col-lg-9 error-msg">
                                <input type="text"  class="form-control"
                                    value="{{$details['dr_nature']}}" readonly>
                            </div>
                        </div>

                        

                    </div>
                </div>

            </div>
            <div class="col-md-6">

                <div class="card bg-white">


                    <div class="card-body">
                        <h3>New Details</h3>
                        @php
                            // dd($details['sub_head']);
                        @endphp
                        <div class="form-group row">
                            <label class="col-form-label col-lg-3">Head Name </label>
                            <div class="col-lg-9 error-msg">
                                <input type="text" name="username" id="username" class=" form-control"
                                    value="{{$newDetails['sub_head']}}" readonly>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-form-label col-lg-3">Parent Id </label>
                            <div class="col-lg-9 error-msg">
                                <input type="text" name="employee_code" id="employee_code" class="form-control"
                                    value="{{$newParentHeadName->sub_head}}" readonly>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-form-label col-lg-3">Label </label>
                            <div class="col-lg-9 error-msg">
                                <input type="text" name="employee_name" id="employee_name" class="form-control"
                                    value="{{$newDetails['labels']}}" readonly>
                            </div>
                        </div>

                        {{-- <div class="form-group row">
                            <label class="col-form-label col-lg-3">Previous Parent Id</label>
                            <div class="col-lg-9 error-msg">
                                <input type="text" class="form-control"
                                    value="{{$newDetails['previous_parent_id']}}" readonly>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-form-label col-lg-3">Parent Auto Id</label>
                            <div class="col-lg-9 error-msg">
                                <input type="text"  class="form-control"
                                    value="{{$newDetails['parentId_auto_id']}}" readonly>
                            </div>
                        </div> --}}

                        <div class="form-group row">
                            <label class="col-form-label col-lg-3">CR Nature </label>
                            <div class="col-lg-9 error-msg">
                                <input type="text"  class="form-control"
                                    value="{{$newDetails['cr_nature']}}" readonly>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-form-label col-lg-3">DR Nature </label>
                            <div class="col-lg-9 error-msg">
                                <input type="text"  class="form-control"
                                    value="{{$newDetails['dr_nature']}}" readonly>
                            </div>
                        </div>

                        

                    </div>
                </div>

            </div>

        </div>
    </div>
    @include('templates.admin.user_management.partials.usermanagement_script')
@stop
