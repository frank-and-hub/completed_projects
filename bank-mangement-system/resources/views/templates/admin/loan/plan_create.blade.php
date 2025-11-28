@extends('templates.admin.master')

@section('content')
<div class="content">
    <div class="row">
        <div class="col-md-12">
            <!--  <div class="card">
                <div class="card-header header-elements-inline">
                    <h6 class="card-title font-weight-semibold">Create</h6>
                </div>
            </div> -->
            <form action="{{route('admin.loan.plan.store')}}" method="post" name="loanplanform" id="loanplanform">
                <div class="card">
                    <div class="card-body">
                        <p class="text-danger"></p>

                        @csrf
                        <input type="hidden" name="created_at" class="created_at">
                        <input type="hidden" name="parent_head_id" id="parent_head_id">
                        <input type="hidden" class="form-control create_application_date "
                            name="create_application_date" id="create_application_date">

                        <div class="form-group row col-sm-12">

                            @include('templates.GlobalTempletes.role_type',['dropDown'=>$company,'filedTitle'=>'Company','name'=>'companyId','apply_col_md'=>true])

                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Type:</label>
                                    <div class="col-lg-12">
                                        <select class="form-control select loan_type " name="loan_type" id="loan_type">
                                            <option value="">--Please Select Type -- </option>
                                            <option value="L">Loan</option>
                                            <option value="G">Group Loan</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-6">Plan Name:</label>
                                    <div class="col-lg-12">
                                        <input type="text" name="name" id="name" class="form-control" autocomplete="off"
                                            value="{{old('name')}}" reqiured>
                                    </div>
                                </div>
                            </div>

                            <!-- <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Code:</label>
                                    <div class="col-lg-12">
                                        <input type="text" name="code" id="code" class="form-control"
                                            value="{{old('code')}}" autocomplete="off" reqiured>
                                    </div>
                                </div>
                            </div> -->
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Category:</label>
                                    <div class="col-lg-12 ">
                                        <select class="form-control  select loan_category" name="loan_category"
                                            id="loan_category">
                                            <option value="">--Please Category -- </option>
                                           {{-- @foreach($loans as $loan)
                                            <option value="{{$loan->id}}" data-head="{{$loan->head_id}}"
                                                class="{{($loan->loan_type == 'L') ? 'loan_cat' : 'grploan_cat' }}"
                                                style="display:none;">{{$loan->name}}</option>

                                            @endforeach--}}

                                        </select>
                                    </div>

                                </div>
                            </div>



                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Minimum amount:</label>
                                    <div class="col-lg-12">
                                        <input type="number" name="min_amount" id="min_amount"
                                            value="{{old('min_amount')}}" min="0" autocomplete="off"
                                            class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Maximum amount:</label>
                                    <div class="col-lg-12">
                                        <input type="number" step="any" name="max_amount" value="{{old('max_amount')}}"
                                            id="max_amount" autocomplete="off" min="0" class="form-control">
                                        <span id="warning-msg" class="text-danger"></span>
                                    </div>

                                </div>

                            </div>


                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Effective From</label>
                                    <div class="col-lg-12">
                                        <input type="text" name="effective_from" id="effective_from"
                                            value="{{old('effective_from')}}" autocomplete="off"
                                            class="form-control effective_from">

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @include('templates.admin.loan.loan_tenure',['type'=>'create'])
                    @include('templates.admin.loan.loan_file_charge',['type'=>'create'])
                    @include('templates.admin.loan.loan_insurance_charge',['type'=>'create'])


                    <div class="text-right" style="padding: 0.5rem;">
                        <button type="submit" class="btn bg-dark">Submit<i class="icon-paperplane ml-2"></i></button>
                    </div>
            </form>
        </div>
    </div>
</div>
@include('templates.admin.loan.partials.settingscript')
@stop