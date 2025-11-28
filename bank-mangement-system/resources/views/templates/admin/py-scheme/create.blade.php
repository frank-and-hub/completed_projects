@extends('templates.admin.master')

@section('content')
<style>
    .select2-selection{
        padding: inherit;
    }
</style>
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">Create</h6>
                    </div>
                    <div class="card-body">
                        <p class="text-danger"></p>
                        <form action="{{ route('admin.plan.store') }}" method="post" name="planform" id="planform" autocomplete="off">
                            @csrf
                            <div class="row">
                                <div class="form-group col-lg-6">
                                    {!!Form::hidden('created_at',null,['class'=>'created_at'])!!}
                                    <label class="col-form-label col-lg-6">Company <sup class="text-danger">*</sup> </label>
                                    <div class="col-lg-12">
                                        <select class="form-control p-0  b-0 company_id" style="padding-bottom:10px !important;" name="company" id="company_id">
                                            <option selected disabled>Company</option>
                                            @if (isset($plan_companies))
                                                @foreach ($plan_companies as $company)
                                                    <option value="{{ $company['id'] }}" @if (old('company') == $company['id']) selected @endif>{{ $company['name'] }}
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
                                        <input type="text" name="name" id="name" class="form-control p-0"
                                            value="{{ old('name') }}">
                                        @error('name')
                                            <label id="name-error" class="error" for="name">{{ $message }}</label>
                                        @enderror
                                        <input type="hidden" name="slug" id="slug" class="form-control p-0">
                                    </div>
                                </div>
                                <div class="form-group col-lg-6">
                                    <label class="col-form-label col-lg-6">Short Name <sup class="text-danger">*</sup>
                                    </label>
                                    <div class="col-lg-12">
                                        <input type="text" name="short_name" id="short_name" class="form-control p-0"
                                             value="{{ old('short_name') }}">
                                        @error('short_name')
                                            <label id="name-error" class="error" for="short_name">{{ $message }}</label>
                                        @enderror
                                    </div>
                                </div>
                                <div class="form-group col-lg-6">
                                    <!-- <div class="col-lg-12">
                                        <input type="hidden" name="plan_code" id="plan_code" class="form-control p-0"
                                             value="{{ old('plan_code') }}">
                                        @error('plan_code')
                                            <label id="name-error" class="error" for="plan_code">{{ $message }}</label>
                                        @enderror
                                    </div> -->

                                    <label class="col-form-label col-lg-6">Plan Category <sup class="text-danger">*</sup>
                                    </label>
                                    <div class="col-lg-12">
                                        <select class="form-control p-0  plan_category" name="plan_category" id="plan_category">
                                            <option selected value="" >Plan Category</option>
                                            @if (isset($categoryies))
                                                @foreach ($categoryies as $category)
                                                    <option value="{{ $category['code'] }}"  @if (old('plan_category') ==  $category['code']) selected @endif>{{ $category['name'] }}
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
                                        <select class="form-control p-0 " name="plan_sub_category">
                                            <option selected value="" > Select Plan Sub Category</option>
                                            @if (isset($subcategoryies))
                                                @foreach ($subcategoryies as $subcategory)
                                                    <option value="{{ $subcategory['code'] }}"  @if (old('plan_sub_category') == $subcategory['code']) selected @endif>{{ $subcategory['name'] }}
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
                                        <select class="form-control p-0 select" name="hybrid_type">
                                            <option selected value="" > Select Hybrid Type</option>
                                            @if (isset($hybrid_type))
                                                @foreach ($hybrid_type as $hybrid)
                                                    <option value="{{ $hybrid['code'] }}"  @if (old('hybrid_type') == $hybrid['code']) selected @endif>{{ $hybrid['name'] }}
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
                                        <input type="number" name="hybrid_tenure" id="hybrid_tenure" min="1"
                                             class="form-control p-0" value="{{ old('hybrid_tenure') }}">
                                        @error('hybrid_tenure')
                                            <label id="name-error" class="error" for="name">{{ $message }}</label>
                                        @enderror
                                    </div>
                                </div>

                                <div class="form-group col-lg-6">
                                    <label class="col-form-label col-lg-6">Minimum Amount <sup class="text-danger">*</sup>
                                    </label>
                                    <div class="col-lg-12">
                                        <input type="number" name="min_amount" id="min_amount" min="0"
                                             class="form-control p-0" value="{{ old('min_amount') }}">
                                        @error('min_amount')
                                            <label id="name-error" class="error" for="name">{{ $message }}</label>
                                        @enderror
                                    </div>
                                </div>
                                <div class="form-group col-lg-6">
                                    <label class="col-form-label col-lg-6">Maximum Amount <sup class="text-danger">*</sup>
                                    </label>
                                    <div class="col-lg-12">
                                        <input type="number" step="any" name="max_amount" id="max_amount"
                                             min="0" class="form-control p-0"
                                            value="{{ old('max_amount') }}">
                                        @error('max_amount')
                                            <label id="name-error" class="error" for="name">{{ $message }}</label>
                                        @enderror
                                    </div>
                                </div>
                                <div class="form-group col-lg-6">
                                    <label class="col-form-label col-lg-6">Multiple Amount<sup
                                            class="text-danger">*</sup> </label>
                                    <div class="col-lg-12">
                                        <input type="number" name="multiple_deposit" id="multiple_deposit"
                                            min="0"  class="form-control p-0"
                                            value="{{ old('multiple_deposit') }}">
                                        @error('multiple_deposit')
                                            <label id="name-error" class="error" for="name">{{ $message }}</label>
                                        @enderror
                                    </div>
                                </div>
                                <div class="form-group col-lg-6">
                                    <label class="col-form-label col-lg-6">Prematurity <sup class="text-danger">*</sup>
                                    </label>
                                    <div class="col-lg-12">
                                        <select class="form-control p-0 " name="prematurity">
                                            <option selected value=""> Select Prematurity Type</option>
                                            <option value="1"  @if (old('prematurity') == 1)  @endif>Allowed
                                            </option>
                                            <option value="0" @if (old('prematurity') == 0)  @endif>Not Allowed
                                            </option>
                                        </select>
                                        
                                    </div>
                                    <div class="col-lg-12">
                                    @error('prematurity')
                                            <label id="name-error" class="error" for="name">{{ $message }}</label>
                                        @enderror
                                    </div>
                                </div>
                                <div class="form-group col-lg-6">
                                    <label class="col-form-label col-lg-6">Loan Against Deposit Type <sup
                                            class="text-danger">*</sup> </label>
                                    <div class="col-lg-12">
                                        <select class="form-control p-0 " name="load_against_deposit">
                                            <option selected value="" > Select Loan Against Deposit</option>
                                            <option value="1" @if (old('load_against_deposit') == 1)  @endif>Allowed
                                            </option>
                                            <option value="0" @if (old('load_against_deposit') == 0)  @endif>Not Allowed
                                            </option>
                                        </select>
                                        @error('load_against_deposit')
                                            <label id="name-error" class="error" for="name">{{ $message }}</label>
                                        @enderror
                                    </div>
                                </div>
                                <div class="form-group col-lg-6">
                                    <label class="col-form-label col-lg-6">Death Help <sup
                                            class="text-danger">*</sup> 
                                    </label>
                                    <div class="col-lg-12">
                                        <select class="form-control p-0 " name="death_help" id="death_help">
                                            <option selected value="" > Select Death Help</option>
                                            <option value="1" @if (old('death_help') == 1)  @endif>Allowed
                                            </option>
                                            <option value="0" @if (old('death_help') == 0) @endif>Not Allowed
                                            </option>
                                        </select>
                                        @error('death_help')
                                            <label id="name-error" class="error" for="name">{{ $message }}</label>
                                        @enderror
                                    </div>
                                </div>
                                <div class="form-group col-lg-6">
                                    <label class="col-form-label col-lg-6">Ssb Required<sup class="text-danger">*</sup>
                                    </label>
                                    <div class="col-lg-12">
                                        <select class="form-control p-0 " name="ssb_required" id="ssb_required">
                                        <option selected value="" > Select Ssb Required</option>
                                            <option value="1" @if (old('ssb_required') == 1)  @endif>Allowed
                                            </option>
                                            <option value="0" @if (old('ssb_required') == 0)  @endif >Not Allowed
                                            </option>
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
                                            <input type="text" class="form-control p-0 create_application_date"
                                                name="effective_from" id="effective_from" readonly=""
                                                value="21/03/2023" aria-invalid="false"
                                                value="{{ old('effective_from') }}">
                                            @error('effective_from')
                                                <label id="name-error" class="error"
                                                    for="name">{{ $message }}</label>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group col-lg-6">
                                    <div class="col-lg-12 text-right">
                                    <button type="submit" class="btn bg-dark ml-2" style="margin-top:40px;">Submit<i
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
