@php
    $companyBranch = app('data');
    $companyBranch = $companyBranch['companyBranch'];
    $designpattern = [1, 2, 3, 4,6];
    $selectedCompany =  (isset($selectedCompany )) ? $selectedCompany : '';
    $selectedBranch = (isset($selectedBranch )) ? $selectedBranch : '';
    $allShow = $allOptionShow ?? false;

@endphp



@if ($design_type === 3)

    @if (isset($apply_col_md) && $apply_col_md)
        <div class="col-md-4">
            <div class="form-group row">
    @endif
    <div class="form-group row member-detail " style="">
        <div class="col-lg-4 error-msg">
            <div class="">
                <select class="form-control select " name="{{ $name }}" id="{{ $name }}"
                    @if ($multiselect) multiple=true @endif title="{{ $placeHolder1 }}" required>
                    <option value="">--Please Select {{ $filedTitle }} -- </option>
                    @if($allShow)
                    <option value="0" > All Company </option>
                    @endif
        @foreach ($dropDown as $key => $dropdown)
                        <option value="{{ $key }}" > {{ $dropdown }} </option>
        @endforeach
                </select>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="">
                <select class="form-control select error-msg" name="{{ $branchName }}" id="branch"
                    @if ($multiselect) multiple=true @endif title="{{ $placeHolder2 }}" required>
                    <option value="">---Please Select Branch --- </option>    
                </select>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="">
                <input type="text" name="{{ $columnName3 }}" id="{{ $columnName3 }}" class="form-control"
                    placeholder="{{ $placeHolder3 }}" required>
            </div>
        </div>
    </div>
    @if (isset($apply_col_md) && $apply_col_md)
        </div>
        </div>
    @endif
@endif
@if ($design_type === 2)
    <div class="col-md-4">
        <div class="form-group row">
            <label class="col-form-label col-lg-6">{{ $filedTitle }} <sup class="required">*</sup></label>
            <div class="col-lg-6 error-msg">
                <select class="form-control" name="{{ $name }}" id="{{ $name }}"
                    @if ($multiselect) multiple=true @endif>
                    @if($allShow)
                    <option value="0" > All Company </option>
                    @endif
                    <option value="">--Please Select {{ $filedTitle }} -- </option>
        @foreach ($dropDown as $key => $dropdown)
                        <option value="{{ $key }}"  > {{ $dropdown }} </option>
        @endforeach
                </select>
            </div>
        </div>
    </div>
@endif
@if (!in_array($design_type, $designpattern))

    @if (isset($apply_col_md) && $apply_col_md)
        <div class="col-md-4">
            <div class="form-group row">
    @endif
    <label
        class="col-form-label @if (isset($apply_col_md) && $apply_col_md) col-lg-12 @else col-lg-2 @endif">{{ $filedTitle }}<sup
            class="required">*</sup> </label>
    <div class="@if (isset($apply_col_md) && $apply_col_md) col-lg-12 @else col-lg-4 @endif error-msg">

        <select class="form-control" name="{{ $name }}" id="{{ $name }}"
            @if ($multiselect) multiple=true @endif>
            
            <option value="">--Please Select {{ $filedTitle }} -- </option>
            @foreach ($dropDown as $key => $dropdown)
                <option value="{{ $key }}" @if(isset($selectedCompany)) {{ ($selectedCompany == $key) ? "selected":""}} @endif> {{ $dropdown }} </option>
            @endforeach
        </select>

    </div>
    @if (isset($apply_col_md) && $apply_col_md)
        </div>
        </div>
    @endif
@endif
@if ($design_type === 4)
 
    <div class="col-md-4">
        <div class="form-group row">
            <label class="col-form-label col-lg-12">{{ $filedTitle }} <sup class="required">*</sup></label>
            <div class="col-lg-12 error-msg">
                
                <select class="form-control" name="{{ $name }}" id="{{ $name }}"
					@if ($multiselect) multiple=true @endif title="{{ $placeHolder1 }}" required>
                    <option value="">--Please Select {{ $filedTitle }} -- </option>
                    @if($allShow)
                    <option value="0" > All Company </option>
                    @endif
                @foreach ($dropDown as $key => $dropdown)
                        <option value="{{ $key }}" > {{ $dropdown }} </option>
                @endforeach
                </select>
                <div class="input-group">
                </div>
            </div>
        </div>
    </div>
@endif
@if ($design_type === 6) 
    <div class="col-md-6">
        <div class="form-group row">
            <label class="col-form-label col-lg-12">{{ $filedTitle }} <sup class="required">*</sup></label>
            <div class="col-lg-12 error-msg">                
                <select class="form-control" name="{{ $name }}" id="{{ $name }}"
                    @if ($multiselect) multiple=true @endif title="{{ $placeHolder1 }}" required>
                    <option value="">--Please Select {{ $filedTitle }} -- </option>
                    @if($allShow)
                    <option value="0" > All Company </option>
                    @endif                    @foreach ($dropDown as $key => $dropdown)
                        <option value="{{ $key }}" > {{ $dropdown }} </option>
                    @endforeach
                </select>
                <div class="input-group">
                </div>
            </div>
        </div>
    </div>
@endif

@if ($branchShow == true)
    @include('templates.GlobalTempletes.branch_filter', [
        'branchName' => $branchName,
        'design_type' => $design_type,
        'placeHolder2' => $placeHolder2,
        'selectedBranch' => $selectedBranch,
        'allShow' => $allOptionShow ?? false,

    ])
@endif




@include('templates.GlobalTempletes.script')
