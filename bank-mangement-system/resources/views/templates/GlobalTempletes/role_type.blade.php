@if (Auth::user()->role_id == 3 && !isset($col))


    <div class="col-md-4">

        <div class="form-group row">

            <label class="col-form-label col-lg-12">{{ $filedTitle ?? '' }} <sup class="required">*</sup></label>

            <div class="col-lg-12 error-msg">

                <select
                    class="form-control @if (isset($multipal) == true) multiselect @endif @if (isset($classes)) {{ $classes }} @endif"
                    name="{{ $name }}" id="{{ $name }}" @if (isset($multipal) == true) multiple @endif
                    @if (isset($form)) form="{{ $form }}" @endif>
                    <option value="">--Please Select {{ $filedTitle ?? '' }} -- </option>
                    @foreach ($dropDown as $key => $dropdown)
                        @if (isset($dropdown->get_company))
                            <option value="{{ $dropdown->get_company->id }}"> {{ $dropdown->get_company->name }}
                            </option>
                        @endif
                    @endforeach
                </select>
            </div>

        </div>

    </div>

@endif
@if (Auth::user()->role_id != 3)
    @if (isset($apply_col_md) && $apply_col_md)
        <div class="col-md-4">
            <div class="form-group row">
    @endif
    <label
        class="col-form-label @if (isset($apply_col_md) && $apply_col_md) col-lg-12 @elseif(isset($col_4))col-lg-4 @else col-lg-2 @endif">{{ $filedTitle }}<sup
            class="required">*</sup> </label>
    <div
        class="@if (isset($apply_col_md) && $apply_col_md) col-lg-12 @elseif(isset($col_4))col-lg-8 @else col-lg-4 @endif error-msg">

        <select class="form-control @if (isset($classes)) {{ $classes }} @endif"
            name="{{ $name }}" id="{{ $name }}">
            <option value="">--Please Select {{ $filedTitle ?? '' }} -- </option>
            @foreach ($dropDown as $key => $dropdown)
                <option value={{ $key }}
                    @php
if (isset($cid) && ($key == $cid)) {
                   echo 'selected';
                   } @endphp>
                    {{ $dropdown }} </option>
            @endforeach
        </select>

    </div>
    @if (isset($apply_col_md) && $apply_col_md)
        </div>
        </div>
    @endif
@endif
@if (Auth::user()->role_id == 3 && isset($col))
    <label class="col-form-label col-lg-2">{{ $filedTitle ?? '' }} <sup class="required">*</sup></label>

    <div class="col-lg-4 error-msg">

        <select
            class="form-control @if (isset($multipal) == true) multiselect @endif @if (isset($classes)) {{ $classes }} @endif"
            name="{{ $name }}" id="{{ $name }}" @if (isset($multipal) == true) multiple @endif
            @if (isset($form)) form="{{ $form }}" @endif>
            <option value="">--Please Select {{ $filedTitle ?? '' }} -- </option>
            @foreach ($dropDown as $key => $dropdown)
                @if (isset($dropdown->get_company))
                    <option value="{{ $dropdown->get_company->id }}"> {{ $dropdown->get_company->name }}
                    </option>
                @endif
            @endforeach
        </select>
    </div>

@endif
