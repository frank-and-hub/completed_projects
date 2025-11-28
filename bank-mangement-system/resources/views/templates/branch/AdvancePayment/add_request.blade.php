@extends('layouts/branch.dashboard')



@section('content')


<div class="container-fluid mt--6">

    <div class="row">

        <div class="col-lg-12">

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


            <form action="#" method="post" enctype="multipart/form-data" id="addrequest" name="fillter">

                @csrf

                <input type="hidden" name="created_at" class="created_at">
                <input type="hidden" name="branch_id" id="branch" value="{{$branch_id}}">

                @php
                $stateid = getBranchState(Auth::user()->username);
                @endphp
                <input type="hidden" class="form-control" name="current_date" id="current_date" value="{{ headerMonthAvailability(date('d'),date('m'),date('Y'),$stateid) }}  ">
                <input type="hidden" class="form-control" name="create_application_date" id="create_application_date" value="{{ headerMonthAvailability(date('d'),date('m'),date('Y'),$stateid) }}  ">
                <div class="row">



                    <div class="col-lg-12">

                        <div class="card bg-white">

                            <div class="card-body">

                                <h3 class="card-title mb-3 maintital">Advance Payment</h3>


                                <div class="row">


                                    @php
                                    $dropDown = $company;
                                    $filedTitle = 'Company';
                                    $name = 'company_id';
                                    @endphp

                                    @include('templates.GlobalTempletes.new_role_type',['dropDown'=>$dropDown,'filedTitle'=>$filedTitle,'name'=>$name,'value'=>'','multiselect'=>'false','design_type'=>4,'branchShow'=>false,'branchName'=>'branch_id','apply_col_md'=>true,'multiselect'=>false,'placeHolder1'=>'Please Select Company','placeHolder2'=>'Please Select Branch'])

                                    <div class="col-md-4">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-12">Advance type<sup class="required">*</sup></label>
                                            <div class="col-lg-12 error-msg">
                                                <select name="paymentType" id="paymentType" class="form-control" aria-invalid="false">

                                                    <option value="">Please Select</option>

                                                    <option data-val="0" value="0">Advance Rent</option>

                                                    <option data-val="1" value="1">Advance Salary</option>

                                                    <option data-val="2" value="2">TA advanced/Imprest</option>

                                                </select>
                                                <div class="input-group">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 d-none">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-12">Company Registration date</label>
                                                <div class="col-lg-12 error-msg">
                                                    <input type="text" name="companyDate" autocomplete="off" id="companyDate" readonly class="form-control">
                                                </div>
                                                <div class="input-group">
                                                </div>
                                        </div>
                                    </div>
                                </div>

                            </div>

                        </div>

                    </div>



                    <!----------------- TA Advance / Imprest ----------------->

                    <div class="col-lg-12 payment-type-box">

                        <div class="card taadvance bg-white" style="display: none;">

                            <div class="card-body">

                                <h4 class="card-title mb-3 heading">Owner Details</h4>

                                <div class="row">



                                    <div class="col-lg-6 employeecode">

                                        <div class="form-group row">

                                            <label class="col-form-label col-lg-4">Employee Code<sup class="required">*</sup></label>

                                            <div class="col-lg-8 error-msg">


                                                <select name="ta_employee_code" id="ta_employee_code" class="form-control input">
                                                    <option value="">Please Select</option>

                                                </select>
                                                <!-- <input type="text" name="ta_employee_code" id="ta_employee_code" class="form-control"> -->

                                            </div>

                                        </div>

                                    </div>
                                    <div class="col-lg-6 ownerlist">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-4">Owner name <sup class="required">*</sup></label>
                                            <div class="col-lg-8 error-msg">
                                                <select name="advanced_rent_party_name" id="advanced_rent_party_name" class="form-control input" data-val="advanced_rent">
                                                    <option value="">Please Select</option>

                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    {{-- Geting created at --}}
                                    <input type="hidden" name="created_at" class="created_at" id="created_at">

                                    <div class="col-lg-6 employeename">

                                        <div class="form-group row">

                                            <label class="col-form-label col-lg-4">Employee Name<sup class="required">*</sup></label>

                                            <div class="col-lg-8 error-msg">

                                                <input type="text" name="ename" readonly id="ename" class="form-control">

                                            </div>

                                        </div>

                                    </div>
                                    {{--Get Employee Id--}}
                                    <input type="hidden" name="member_id" id="member_id" class="form-control">
                                    <input type="hidden" name="associate_code" id="associate_code" class="form-control">
                                    <input type="hidden" name="employee_id" id="employee_id" value="" class="form-control">
                                    <input type="hidden" name="employee_name" id="employee_name" class="form-control">
                                    <input type="hidden" name="accountNumber" id="accountNumber" class="form-control">

                                    <div class="col-lg-6">

                                        <div class="form-group row">

                                            <label class="col-form-label col-lg-4">Date<sup class="required">*</sup></label>

                                            <div class="col-lg-8 error-msg">

                                                <input type="text" name="date" autocomplete="off" id="date" value="{{headerMonthAvailability(date('d'),date('m'),date('Y'),$stateid)}}" readonly class="form-control">

                                            </div>

                                        </div>

                                    </div>
                                    <div class="col-lg-6">

                                        <div class="form-group row">

                                            <label class="col-form-label col-lg-4">Narration<sup class="required">*</sup></label>

                                            <div class="col-lg-8 error-msg">

                                                <input type="text" name="narration" required id="narration" class="form-control">

                                            </div>

                                        </div>

                                    </div>


                                    <div class="col-lg-6">

                                        <div class="form-group row">

                                            <label class="col-form-label col-lg-4 amount">Amount<sup class="required">*</sup></label>

                                            <div class="col-lg-8 error-msg">

                                                <input type="text" name="aamount" id="aamount" required class="form-control">

                                            </div>

                                        </div>

                                    </div>

                                    <div class="col-lg-6">

                                        <div class="form-group row">

                                            <label class="col-form-label col-lg-4">Mobile Number <sup class="required">*</sup></label>

                                            <div class="col-lg-8 error-msg">

                                                <input type="text" required readonly name="advanced_salary_mobile_number2" id="advanced_salary_mobile_number2" class="form-control input">

                                            </div>

                                        </div>

                                    </div>

                                    <div class="col-lg-6">

                                        <div class="form-group row">

                                            <label class="col-form-label col-lg-4">Bank Name <sup class="required">*</sup></label>

                                            <div class="col-lg-8 error-msg">

                                                <input type="text" required readonly name="advanced_salary_bank_name2" id="advanced_salary_bank_name2" class="form-control input" readonly="">

                                            </div>

                                        </div>

                                    </div>



                                    <div class="col-lg-6">

                                        <div class="form-group row">

                                            <label class="col-form-label col-lg-4">Bank A/C No. <sup class="required">*</sup></label>

                                            <div class="col-lg-8 error-msg">

                                                <input type="text" required readonly name="advanced_salary_bank_account_number2" id="advanced_salary_bank_account_number2" class="form-control input" readonly="">

                                            </div>

                                        </div>

                                    </div>



                                    <div class="col-lg-6">

                                        <div class="form-group row">

                                            <label class="col-form-label col-lg-4">IFSC Code <sup class="required">*</sup></label>

                                            <div class="col-lg-8 error-msg">

                                                <input type="text" required readonly name="advanced_salary_ifsc_code2" id="advanced_salary_ifsc_code2" class="form-control input" readonly="">

                                            </div>

                                        </div>

                                    </div>

                                    <div class="col-lg-6" id="ssb">

                                        <div class="form-group row">

                                            <label class="col-form-label col-lg-4">SSB Account Number</label>

                                            <div class="col-lg-8 error-msg">

                                                <input type="text" readonly name="ssbno" id="ssbno" class="form-control">

                                            </div>

                                        </div>

                                    </div>

                                    <div class="col-lg-6" id="branchBalance2" style="display:none;">

                                        <div class="form-group row">

                                            <label class="col-form-label col-lg-4">Branch Current Balance<sup class="required">*</sup></label>

                                            <div class="col-lg-8 error-msg">

                                                <input type="text" readonly name="branchBalance" id="branchBalance" class="form-control">

                                            </div>

                                        </div>

                                    </div>

                                    <div class="col-lg-6">

                                        <div class="form-group row">

                                            <label class="col-form-label col-lg-4">File</label>

                                            <div class="col-lg-8 error-msg">

                                                <input type="file" name="file" id="file" class="form-control">

                                            </div>

                                        </div>

                                    </div>


                                </div>


                                <!----------------- End TA Advance / Imprest ----------------->


                                <button type="sumbit" id="tasubmit" class="btn btn-primary text-right float-right">Submit</button>
                            </div>
                        </div>
                    </div>
            </form>

        </div>

    </div>

</div>




@stop

@section('script')
@include('templates.branch.AdvancePayment.partials.script_add_request')

@stop