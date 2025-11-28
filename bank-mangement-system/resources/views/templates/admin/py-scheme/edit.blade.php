@extends('templates.admin.master')

@section('content')
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">Edit</h6>
                    </div>
                    <div class="card-body">
                        <p class="text-danger"></p>
                        <form action="{{ route('admin.plan.update') }}" method="post" name="planform" id="planform">
                            @csrf
                            <div class="row">
                                <div class="form-group col-lg-6">
                                    {!!Form::hidden('created_at',null,['class'=>'created_at'])!!}
                                    <label class="col-form-label col-lg-6">Company <sup class="text-danger">*</sup> </label>
                                    <div class="col-lg-12">
                                        <select class="form-control select" name="company">
                                            <option selected disabled>Company</option>
                                            @if (isset($plan_companies))
                                                @foreach ($plan_companies as $company)
                                                    <option value="{{ $company['id'] }}"
                                                        {{ $company['id'] == $plan->company_id ? 'selected' : '' }}>
                                                        {{ $company['name'] }}
                                                    </option>
                                                @endforeach
                                            @endif
                                        </select>
                                        @error('company')
                                            <label id="name-error" class="error" for="name">{{ $message }}</label>
                                        @enderror
                                    </div>

                                </div>
                                <div class="form-group col-lg-6">
                                    <label class="col-form-label col-lg-6">Name <sup class="text-danger">*</sup> </label>
                                    <div class="col-lg-12">
                                        <input type="text" name="name" id="name" class="form-control"
                                            value="{{ $plan->name }}">
                                        <input type="hidden" name="slug" value="{{ $plan->slug }}">
                                    </div>
                                </div>
                                <div class="form-group col-lg-6">
                                    <label class="col-form-label col-lg-6">Short Name </label>
                                    <div class="col-lg-12">
                                        <input type="text" name="short_name" id="short_name" class="form-control"
                                            autocomplete="off" value="{{ $plan->short_name }}" disabled>
                                        @error('short_name')
                                            <label id="name-error" class="error" for="short_name">{{ $message }}</label>
                                        @enderror
                                    </div>
                                </div>
                                <div class="form-group col-lg-6">
                                    <label class="col-form-label col-lg-6">Plan Code </label>
                                    <div class="col-lg-12">
                                        <input type="text" name="plan_code" id="plan_code" class="form-control"
                                            value="{{ $plan->plan_code }}" readonly>
                                    </div>
                                </div>
                                <div class="form-group col-lg-6">
                                    <label class="col-form-label col-lg-6">Plan Category <sup class="text-danger">*</sup>
                                    </label>
                                    <div class="col-lg-12">
                                        <select class="form-control select plan_category" name="plan_category">
                                            <option selected disabled>Plan Category</option>
                                            @if (isset($categoryies))
                                                @foreach ($categoryies as $category)
                                                    <option value="{{ $category['code'] }}"
                                                        {{ $category['code'] == $plan->plan_category_code ? 'selected' : '' }}>
                                                        {{ $category['name'] }}
                                                    </option>
                                                @endforeach
                                            @endif
                                        </select>
                                        @error('plan_category')
                                            <label id="name-error" class="error" for="name">{{ $message }}</label>
                                        @enderror
                                    </div>
                                </div>
                                <div class="form-group col-lg-6">
                                    <label class="col-form-label col-lg-6">Plan Sub Category</label>
                                    <div class="col-lg-12">
                                        <select class="form-control select plan_sub_category" name="plan_sub_category">
                                            <option selected disabled>Sub Category</option>
                                            @if (isset($subcategoryies))
                                                @foreach ($subcategoryies as $subcategory)
                                                    <option value="{{ $subcategory['code'] }}"
                                                        {{ $subcategory['code'] == $plan->plan_sub_category_code ? 'selected' : '' }}>
                                                        {{ $subcategory['name'] }}
                                                    </option>
                                                @endforeach
                                            @endif
                                            @error('plan_sub_category')
                                                <label id="name-error" class="error"
                                                    for="name">{{ $message }}</label>
                                            @enderror
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group col-lg-6">
                                    <label class="col-form-label col-lg-6">Hybrid Type</label>
                                    <div class="col-lg-12">
                                        <select class="form-control select hybrid_type" name="hybrid_type">
                                            <option selected disabled>Hybrid Type</option>
                                            @if (isset($hybrid_type))
                                                @foreach ($hybrid_type as $hybrid)
                                                    <option
                                                        value="{{ $hybrid['code'] }}"{{ $hybrid['code'] == $plan->hybrid_type ? 'selected' : '' }}>
                                                        {{ $hybrid['name'] }}
                                                    </option>
                                                @endforeach
                                            @endif
                                        </select>
                                        @error('hybrid_type')
                                            <label id="name-error" class="error" for="name">{{ $message }}</label>
                                        @enderror
                                    </div>
                                </div>
                                <div class="form-group col-lg-6">
                                    <label class="col-form-label col-lg-6">Hybrid Tenure</label>
                                    <div class="col-lg-12">
                                        <input type="number" name="hybrid_tenure" id="hybrid_tenure" min="0"
                                            autocomplete="off" class="form-control" value="{{ $plan->hybrid_tenure }}">
                                        @error('hybrid_tenure')
                                            <label id="name-error" class="error" for="name">{{ $message }}</label>
                                        @enderror
                                    </div>
                                </div>
                                <div class="form-group col-lg-6">
                                    <label class="col-form-label col-lg-6">Minimum Amount <sup class="text-danger">*</sup>
                                    </label>
                                    <div class="col-lg-12">
                                        <input type="number" step="any" name="min_amount" id="min_amount"
                                            min="0" value="{{ $plan->min_deposit }}" class="form-control"
                                            autocomplete="off">
                                        @error('min_amount')
                                            <label id="name-error" class="error" for="name">{{ $message }}</label>
                                        @enderror
                                    </div>
                                </div>
                                <div class="form-group col-lg-6">
                                    <label class="col-form-label col-lg-6">Multiple Deposit <sup
                                            class="text-danger">*</sup> </label>
                                    <div class="col-lg-12">
                                        <input type="number" name="multiple_deposit" id="multiple_deposit"
                                            min="0" autocomplete="off" class="form-control"
                                            value="{{ $plan->multiple_deposit }}">
                                        @error('multiple_deposit')
                                            <label id="name-error" class="error" for="name">{{ $message }}</label>
                                        @enderror
                                    </div>
                                </div>
                                <div class="form-group col-lg-6">
                                    <label class="col-form-label col-lg-6">Maximum Amount <sup class="text-danger">*</sup>
                                    </label>
                                    <div class="col-lg-12">
                                        <input type="number" step="any" name="max_amount" id="max_amount"
                                            min="0" value="{{ $plan->max_deposit }}" class="form-control"
                                            autocomplete="off">
                                        @error('max_amount')
                                            <label id="name-error" class="error" for="name">{{ $message }}</label>
                                        @enderror
                                    </div>
                                </div>
                                <div class="form-group col-lg-6">
                                    <label class="col-form-label col-lg-6">Prematurity <sup class="text-danger">*</sup>
                                    </label>
                                    <div class="col-lg-12">
                                        <select class="form-control select prematurity" name="prematurity">
                                            <option selected disabled>Prematurity</option>
                                            <option value="1" {{ $plan->prematurity == 1 ? 'selected' : '' }}>Allowed
                                            </option>
                                            <option value="0" {{ $plan->prematurity == 0 ? 'selected' : '' }}>Not
                                                Allowed</option>
                                        </select>
                                        @error('prematurity')
                                            <label id="name-error" class="error" for="name">{{ $message }}</label>
                                        @enderror
                                    </div>
                                </div>
                                <div class="form-group col-lg-6">
                                    <label class="col-form-label col-lg-6">Loan Against Deposit <sup
                                            class="text-danger">*</sup> </label>
                                    <div class="col-lg-12">
                                        <select class="form-control select load_against_deposit" name="load_against_deposit">
                                            <option selected disabled>Loan Against Deposit</option>
                                            <option value="1"
                                                {{ $plan->loan_against_deposit == 1 ? 'selected' : '' }}>Allowed</option>
                                            <option value="0"
                                                {{ $plan->loan_against_deposit == 0 ? 'selected' : '' }}>Not Allowed
                                            </option>
                                        </select>
                                        @error('load_against_deposit')
                                            <label id="name-error" class="error" for="name">{{ $message }}</label>
                                        @enderror
                                    </div>
                                </div>
                                <div class="form-group col-lg-6">
                                    <label class="col-form-label col-lg-6">Death Help <sup class="text-danger">*</sup>
                                    </label>
                                    <div class="col-lg-12">
                                        <select class="form-control select death_help" name="death_help">
                                            <option selected disabled>Death Help</option>
                                            <option value="1" {{ $plan->death_help == 1 ? 'selected' : '' }}>Allowed
                                            </option>
                                            <option value="0" {{ $plan->death_help == 0 ? 'selected' : '' }}>Not
                                                Allowed</option>
                                        </select>
                                        @error('death_help')
                                            <label id="name-error" class="error" for="name">{{ $message }}</label>
                                        @enderror
                                    </div>
                                </div>
                                <div class="form-group col-lg-6">
                                    <label class="col-form-label col-lg-6">SSB Required<sup class="text-danger">*</sup>
                                    </label>
                                    <div class="col-lg-12">
                                        <select class="form-control select ssb_required" name="ssb_required">
                                            <option selected disabled>SSB Required</option>
                                            <option value="1" {{ $plan->is_ssb_required == 1 ? 'selected' : '' }}>Allowed
                                            </option>
                                            <option value="0" {{ $plan->is_ssb_required == 0 ? 'selected' : '' }}>Not
                                                Allowed</option>
                                        </select>
                                        @error('ssb_required')
                                            <label id="name-error" class="error" for="name">{{ $message }}</label>
                                        @enderror
                                    </div>
                                </div>
                                <div class="form-group col-lg-6">
                                    <label class="col-form-label col-lg-6">Effective From <sup class="text-danger">*</sup>
                                    </label>
                                    <div class="col-lg-12 error-msg">
                                        <div class="input-group">
                                            <span class="input-group-prepend">
                                                <span class="input-group-text"><i class="fa fa-calendar"
                                                        aria-hidden="true"></i></span>
                                            </span>
                                            <input type="text" class="form-control "
                                                name="effective_from" id="effective_from" readonly="" aria-invalid="false"
                                                value='{{ date("d/m/Y", strtotime($plan->effective_from)) }} '>
                                            @error('effective_from')
                                                <label id="name-error" class="error"
                                                    for="name">{{ $message }}</label>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group col-lg-6">
                                    <div class="col-lg-12 text-right">
                                    <button type="submit" class="btn bg-dark ml-2" style="margin-top:40px;">Update<i
                                            class="icon-paperplane ml-2"></i></button></div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('templates.admin.py-scheme.partials.script')
@stop
