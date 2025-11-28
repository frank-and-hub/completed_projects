@extends('templates.admin.master')
@section('content')
    <div class="content">
        <div class="row">
            <div id="modelDiv"></div>
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">Add New Tenure</h6>
                    </div>
                    <div class="card-body">
                        <p class="text-danger"></p>
                        {{ Form::open(['url' => route('admin.py-plans.tenure.tenure_save'), 'method' => 'POST', 'id' => 'tenure_form', 'name' => 'tenure_form']) }}
                        <div class="row">
                            <div class="form-group col-lg-4">
                                <label class="col-form-label col-lg-6 pl-0">Plan <sup class="text-danger">*</sup> </label>
                                {!! Form::text('plan_id', $plan->name ?? old('plan_id'), ['class' => 'form-control readonly', 'id' => 'plan_id' ,'readonly' => true]) !!}
                                <div class="col-lg-10">
                                    <div class="form-group col-lg-12">
                                        <div class="col-lg-10">
                                            @error('plan_id')
                                                <label id="plan_id-error" class="error" for="plan_id">{{ $message }}</label>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group col-lg-4">
                                <label class="col-form-label col-lg-6 pl-0">Plan code <sup class="text-danger">*</sup> </label>
                                {!! Form::text('plan_code', $plan->plan_code ?? old('plan_code'), ['class' => 'form-control readonly', 'id' => 'plan_code' , 'readonly' => true]) !!}
                                <div class="col-lg-10">
                                    <div class="form-group col-lg-12">
                                        <div class="col-lg-10">
                                            @error('plan_code')
                                                <label id="plan_code-error" class="error" for="plan_code">{{ $message }}</label>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group col-lg-4">
                                <label class="col-form-label col-lg-6 pl-0">Tenure <sup class="text-danger">*</sup> </label>
                                {!! Form::text('tenure', old('tenure'), ['class' => 'form-control', 'id' => 'tenure']) !!}
                                <div class="col-lg-10">
                                    <div class="form-group col-lg-12">
                                        <div class="col-lg-10">
                                            @error('tenure')
                                                <label id="tenure-error" class="error" for="tenure">{{ $message }}</label>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group col-lg-4">
                                <label class="col-form-label col-lg-6 pl-0">Month From <sup class="text-danger">*</sup>
                                </label>
                                {!! Form::text('month_from', old('month_from'), ['class' => 'form-control', 'id' => 'month_from']) !!}
                                <div class="col-lg-10">
                                    <div class="form-group col-lg-12">
                                        <div class="col-lg-10">
                                            @error('month_from')
                                                <label id="month_from-error" class="error" for="month_from">{{ $message }}</label>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group col-lg-4">
                                <label class="col-form-label col-lg-6 pl-0">Month To <sup class="text-danger">*</sup>
                                </label>
                                {!! Form::text('month_to', old('month_to'), ['class' => 'form-control', 'id' => 'month_to']) !!}
                                <div class="col-lg-10">
                                    <div class="form-group col-lg-12">
                                        <div class="col-lg-10">
                                            @error('month_to')
                                                <label id="month_to-error" class="error" for="month_to">{{ $message }}</label>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group col-lg-4">
                                <label class="col-form-label col-lg-6 pl-0">ROI <sup class="text-danger">*</sup> </label>
                                {!! Form::text('roi', old('roi') ?? '0.00', ['class' => 'form-control', 'id' => 'roi']) !!}
                                <div class="col-lg-10">
                                    <div class="form-group col-lg-12">
                                        <div class="col-lg-10">
                                            @error('roi')
                                                <label id="roi-error" class="error" for="roi">{{ $message }}</label>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group col-lg-4">
                                <label class="col-form-label col-lg-6 pl-0">SPL ROI <sup class="text-danger">*</sup>
                                </label>
                                {!! Form::text('spl_roi', old('spl_roi') ?? '0.00', ['class' => 'form-control', 'id' => 'spl_roi']) !!}
                                <div class="col-lg-10">
                                    <div class="form-group col-lg-12">
                                        <div class="col-lg-10">
                                            @error('spl_roi')
                                                <label id="spl_roi-error" class="error" for="spl_roi">{{ $message }}</label>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group col-lg-4">
                                <label class="col-form-label col-lg-6 pl-0">Compounding <sup class="text-danger">*</sup>
                                </label>
                                {!! Form::select('compounding',['3' => 'Quarterly', '6' => 'Half Yearly', '12' => 'Annually'], old('compounding'),['class' => 'form-control select'],) !!}
                                <div class="col-lg-10">
                                    <div class="form-group col-lg-12">
                                        <div class="col-lg-10">
                                            @error('compounding')
                                                <label id="compounding-error" class="error" for="compounding">{{ $message }}</label>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group col-lg-4">
                                <label class="col-form-label col-lg-6 pl-0">Effective From<sup class="text-danger">*</sup>
                                </label>
                                {!! Form::text('effective_from', old('effective_from'), ['class' => 'form-control', 'id' => 'effective_from']) !!}
                                <div class="col-lg-10">
                                    <div class="form-group col-lg-12">
                                        <div class="col-lg-10">
                                            @error('effective_from')
                                                <label id="effective_from-error" class="error" for="effective_from">{{ $message }}</label>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group col-lg-12 pr-0">
                            {!! Form::hidden('plan_code', $plan->plan_code ?? '') !!}
                            {!! Form::hidden('plan_id', $plan->id ?? '') !!}
                            {!! Form::hidden('searchform', 'yes', ['id' => 'searchform', 'class' => '']) !!}
                            {!! Form::hidden('is_search', 'yes', ['id' => 'is_search', 'class' => 'is_search']) !!}
                            {!! Form::hidden('created_by_id', Auth::user()->id) !!}
                            <button type="submit" class="btn bg-dark ml-2 float-right">Submit</button>
                        </div>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <div class="card">
                    <div class="">
                        <table id="investment_plans_tenure" class="table datatable-show-all">
                            <thead>
                                <tr>
                                    <th>S.N</th>
                                    {{-- <th>Plan Code</th> --}}
                                    <th>Plan</th>
                                    <th>Tenure</th>
                                    <th>ROI</th>
                                    <th>SPL ROI</th>
                                    <th>Compounding</th>
                                    <th>Status</th>
                                    <th>Effective From</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

@include('templates.admin.py-scheme.partials.script')
@stop
