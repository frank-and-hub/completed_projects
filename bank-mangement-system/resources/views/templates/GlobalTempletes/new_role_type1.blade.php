@if (Auth::user()->role_id == 3 && !isset($col))


    <div class="col-md-6">

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