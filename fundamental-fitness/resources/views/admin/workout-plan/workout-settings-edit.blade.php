@extends('admin.layout.index')
@section('content')
@section('admin-title', 'Edit Workout Settings')

@section('content')
    <div class="container-fluid">
        <div class="pate-content-wrapper">
            <div class="page-title-row">
                <h5>Select</h5>
            </div>
            <div class="m-card-min-hight">
                <form method="POST" action="{{ route('admin.workoutSettingsUpdate', $workoutProgramId) }}">
                    @csrf
                    @method('POST')

                    <div class="row">
                        @foreach ($questions as $question)
                            <div class="col-md-6 mb-3">
                                @php
                                    $rawTitle = trim($question->title_for_web); // remove extra spaces
                                    $hasAsterisk = substr($rawTitle, -1) === '*'; // check if ends with *
                                    $title = $hasAsterisk ? rtrim($rawTitle, '*') : $rawTitle; // remove trailing *
                                @endphp
                                <label for="question_{{ $question->id }}" class="text-14 font-500">
                                    {{ $title }} 
                                    @if($hasAsterisk)
                                        <span class="text-danger" style="color:red">*</span>
                                    @endif
                                </label>
                                <div class="checkbox-bg">
                                    @foreach ($question->options as $option)
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input"
                                                type="{{ $question->type_for_web == 2 ? 'checkbox' : 'radio' }}"
                                                id="option_{{ $option->id }}"
                                                name="responses[{{ $question->id }}]{{ $question->type_for_web == 2 ? '[]' : '' }}"
                                                value="{{ $option->id }}"
                                                @php $saved = $savedResponses[$question->id] ?? [];
                                            $isChecked = ($question->type_for_web == 2) 
                                            ? in_array($option->id, $saved) 
                                            : (count($saved) && $saved[0] == $option->id); @endphp
                                                {{ $isChecked ? 'checked' : '' }}
                                                {{ $question->type_for_web == 1 ? 'required' : '' }}>
                                            <label class="form-check-label" for="option_{{ $option->id }}">
                                                {{ $option->label_for_web }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>

                                @error("responses.{$question->id}")
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
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
                            <a href="{{ route('admin.workoutPlansEdit', $workoutProgramId) }}" class="btn btn-outline-primary btn-sm me-2">Back</a>
                            <button type="submit" name="action" value="next" class="btn btn-primary btn-sm">Next</button>
                            {{-- <button type="submit" name="action" value="save" class="btn btn-primary btn-sm">Save</button> --}}
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
    </style>

@endsection
