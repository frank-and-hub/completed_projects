@extends('admin.layout.index')
@section('content')
@section('admin-title', 'Workout Settings')

@section('content')
    <div class="container-fluid">
        <div class="pate-content-wrapper">
            <div class="page-title-row">
                <h5>Select</h5>
            </div>
            <div class="m-card-min-hight">
                <form method="POST" action="{{ route('admin.workoutupdate', $programId) }}">
                    @csrf
                    @method('POST')

                    <div class="row">
                        @foreach ($questions as $question)
                            @php
                                $rawTitle = trim($question->title_for_web); // remove extra spaces
                                $hasAsterisk = substr($rawTitle, -1) === '*'; // check if ends with *
                                $title = $hasAsterisk ? rtrim($rawTitle, '*') : $rawTitle; // remove trailing *
                            @endphp
                            <div class="col-md-6 mb-3">
                                {{-- <label for="question_{{ $question->id }}" class="text-14 font-500">
                                    {{ $question->title_for_web }} <span class="text-danger"></span>
                                </label> --}}
                                <div class="question-wrapper" data-question-id="{{ $question->id }}">
                                    <label>{{ $title }} @if ($hasAsterisk)
                                            <span class="text-danger">*</span>
                                        @endif
                                    </label>
                                    <div class="checkbox-bg">
                                        @foreach ($question->options as $option)
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input"
                                                    type="{{ $question->type_for_web == 2 ? 'checkbox' : 'radio' }}"
                                                    name="responses[{{ $question->id }}]{{ $question->type_for_web == 2 ? '[]' : '' }}"
                                                    value="{{ $option->id }}">
                                                <label class="form-check-label">{{ $option->label_for_web }}</label>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="error-message text-danger" style="display:none;"></div>
                                </div>

                            </div>
                        @endforeach

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="text-end mb-3">
                            <a href="{{ route('admin.workoutPlansEdit', $programId) }}"
                                class="btn btn-outline-primary btn-sm me-2">Back</a>
                            <button type="submit" id="submit" class="btn btn-primary btn-sm">Next</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        .checkbox-bg {
            background: #fff;
            padding: 10px;
            border-radius: 5px;
            margin-top: 5px;
        }

        .form-check-inline {
            margin-right: 15px;
        }

        .form-check-input {
            margin-top: 0.25em;
        }

        .text-14 {
            font-weight: 600;
            display: block;
            margin-bottom: 8px;
        }

        .page-title-row h5 {
            font-size: 20px;
            margin-bottom: 20px;
        }

        .m-card-min-hight {
            background: #f6f6f6;
            padding: 25px;
            border-radius: 10px;
        }

        .text-end .btn {
            min-width: 120px;
        }

        .has-error {
            /* border: 1px solid red; */
            /* padding: 10px; */
            border-radius: 5px;
        }
    </style>

    @push('script')
        <script>
            $('#submit').on('click', function(e) {
                let isValid = true;

                const $question = $('[data-question-id="15"]'); //  actual ID
                const $checked = $question.find('.form-check-input:checked');

                $question.find('.error-message').hide(); // Clear previous errors
                $question.removeClass('has-error');

                if ($checked.length === 0) {
                    isValid = false;
                    $question.addClass('has-error');
                    $question.find('.error-message').text('Please select one of these options.').show();
                }

                if (!isValid) {
                    e.preventDefault();
                }
            });

            $(document).on('change', '[data-question-id="15"] .form-check-input', function() {
                const $question = $('[data-question-id="15"]');
                const $checked = $question.find('.form-check-input:checked');

                if ($checked.length > 0) {
                    $question.removeClass('has-error');
                    $question.find('.error-message').hide();
                }
            });
        </script>
    @endpush


@endsection
