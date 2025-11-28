@extends('admin.layout.index')

@section('admin-title', 'Add Meal')

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .card {
            border: none;
        }

        .form-select,
        .form-control {
            border-radius: 8px;
            font-size: 15px;
        }

        .meal-section label {
            font-size: 16px;
            margin-bottom: 6px;
        }

        .chips-container {
            min-height: 36px;
        }

        .chip {
            display: inline-block;
            background: #fff0f0;
            color: #b30000;
            border-radius: 16px;
            padding: 6px 14px 6px 12px;
            margin: 0 8px 8px 0;
            font-size: 14px;
            font-weight: 500;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
            position: relative;
        }

        .remove-chip {
            margin-left: 8px;
            cursor: pointer;
            color: #b30000;
            font-weight: bold;
            font-size: 16px;
        }

        .btn-outline-danger {
            color: #b30000;
            border-color: #ffcccc;
            background: #fff0f0;
        }

        .btn-outline-danger:hover {
            background: #ffe5e5;
            color: #fff;
        }

        .btn-outline-primary,
        .btn-primary {
            border-radius: 8px;
        }

        .btn-outline-primary {
            border-color: #ff7043;
            color: #ff7043;
        }

        .btn-outline-primary:hover {
            background: #ff7043;
            color: #fff;
        }

        .btn-primary {
            background: #ff7043;
            border-color: #ff7043;
        }

        .btn-primary:hover {
            background: #e65100;
            border-color: #e65100;
        }

        .select2-container--default .select2-results>.select2-results__options {
            max-height: 200px;
            overflow-y: auto;
            padding: 5px;
        }

        .select2-container--default .select2-results__option {
            padding: 10px 12px;
            border-radius: 8px;
            margin: 3px 0;
            transition: background 0.2s;
            font-size: 14px;
        }

        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: #ffe5e5;
            color: #b30000;
        }

        .select2-container--default .select2-results__option[aria-selected=true] {
            background-color: #fff0f0;
            color: #cc0000;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background-color: #ffffff !important;
            border: 1px solid #d9d9d9 !important;
            border-radius: 30px !important;
            padding-left: 0 !important;
            padding-right: 10px !important;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            border-right: 0 !important;
            border-top-left-radius: 0 !important;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            background-color: #333 !important;
            left: auto !important;
            right: 0 !important;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice__display {
            padding-left: 8px !important;
            padding-right: 20px !important;
            font-size: 12px !important;
            font-weight: 500;
            color: #333 !important;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            background-color: #ff7043 !important;
            left: auto !important;
            right: 0 !important;
            border-radius: 50% !important;
            margin-right: 5px;
            width: 16px !important;
            height: 16px !important;
            font-size: 11px !important;
            color: #fff !important;
            top: 2px;
        }

        .toast {
            border-radius: 8px !important;
        }

        .toast-success {
            background-color: #51A351 !important;
        }

        .toast-error {
            background-color: #BD362F !important;
        }

        .toast-info {
            background-color: #2F96B4 !important;
        }

        .toast-warning {
            background-color: #F89406 !important;
        }

        select option:disabled {
            color: #ccc;
            background-color: #f8f8f8;
        }

        select option[disabled]:first-child {
            color: #333;
        }

        .diet-type-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 2px solid #ff7043;
            color: #333;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid d-flex justify-content-center align-items-center"
        style="min-height: 90vh; background: #f5f5f5;">
        <div class="card shadow-lg p-4" style="border-radius: 18px; width: 100%; background: #fff;">
            <h3 class="mb-4" style="font-weight: 600;">Create Meals</h3>
            <form action="{{ route('admin.saveUserMeals', $programId) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('POST')

                <!-- Meal Sections Wrapper -->
                <div id="meal-sections-wrapper">
                    @php
                        $requiredDietPreferences = ['Veg', 'Vegan', 'Keto', 'Mixed'];
                        $currentIndex = 0;
                        $vegId = null;
                        $nonVegId = null;

                        // Get Veg and Non-Veg IDs for Mixed meals
                        foreach ($mealDietPreferences as $pref) {
                            if (strtolower($pref->name) == 'veg') {
                                $vegId = $pref->id;
                            } elseif (strtolower($pref->name) == 'non-veg') {
                                $nonVegId = $pref->id;
                            }
                        }
                    @endphp

                    @foreach ($requiredDietPreferences as $dietPreference)
                        @php
                            $dietPrefId = null;
                            if (strtolower($dietPreference) == 'mixed') {
                                // For Mixed, we'll use both Veg and Non-Veg IDs
                                $dietPrefId = [$vegId, $nonVegId];
                            } else {
                                foreach ($mealDietPreferences as $pref) {
                                    if (strtolower($pref->name) == strtolower($dietPreference)) {
                                        $dietPrefId = $pref->id;
                                        break;
                                    }
                                }
                            }
                        @endphp

                        <div class="meal-section-group mb-4 p-3" style="background: #fcf6f5; border-radius: 12px;">
                            <div class="diet-type-title">{{ $dietPreference }} Meals</div>
                            @if (is_array($dietPrefId))
                                <input type="hidden" name="meal[{{ $currentIndex }}][diet_preference]"
                                    value="{{ implode(',', $dietPrefId) }}">
                            @else
                                <input type="hidden" name="meal[{{ $currentIndex }}][diet_preference]"
                                    value="{{ $dietPrefId }}">
                            @endif

                            <div class="meal-section mb-4">
                                <label class="fw-bold">Breakfast</label>
                                <select class="form-control meal_title" name="meal[{{ $currentIndex }}][breakfast][]"
                                    multiple>
                                </select>
                            </div>
                            <div class="meal-section mb-4">
                                <label class="fw-bold">Lunch</label>
                                <select class="form-control meal_title" name="meal[{{ $currentIndex }}][lunch][]"
                                    multiple></select>
                            </div>
                            <div class="meal-section mb-4">
                                <label class="fw-bold">Dinner</label>
                                <select class="form-control meal_title" name="meal[{{ $currentIndex }}][dinner][]"
                                    multiple></select>
                            </div>
                        </div>
                        @php $currentIndex++; @endphp
                    @endforeach
                </div>

                <!-- Submit Button -->
                <div class="mt-4 d-flex justify-content-end">
                    <a href="{{ route('admin.workoutSettingsEdit', $programId) }}"
                        class="btn btn-outline-primary btn-sm me-2" style="border-radius: 8px;">Back</a>
                    <button type="submit" id="submitBtn" class="btn btn-primary btn-sm" style="border-radius: 8px;">Create
                        Meal Plan</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('script')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <script>
        $(document).ready(function() {
            // Initialize Select2 on all meal selects
            $('.meal_title').select2({
                placeholder: 'Select meal(s)',
                width: '100%'
            });

            // Load meals for each diet preference on page load
            @foreach ($requiredDietPreferences as $index => $dietPreference)
                @php
                    if (strtolower($dietPreference) == 'mixed') {
                        // For Mixed, we'll use both Veg and Non-Veg IDs
                        $dietPrefIds = [$vegId, $nonVegId];
                    } else {
                        $dietPrefId = null;
                        foreach ($mealDietPreferences as $pref) {
                            if (strtolower($pref->name) == strtolower($dietPreference)) {
                                $dietPrefId = $pref->id;
                                break;
                            }
                        }
                        $dietPrefIds = [$dietPrefId];
                    }
                @endphp

                @if (!empty(array_filter($dietPrefIds)))
                    loadMealsForDietPreference({{ $index }}, [
                        @foreach ($dietPrefIds as $id)
                            {{ $id }}@if (!$loop->last),@endif
                        @endforeach
                    ]);
                @endif
            @endforeach

            // Function to load meals for a specific diet preference (or multiple preferences)
            function loadMealsForDietPreference(index, dietIds) {
                dietIds = dietIds.filter(id => id != null); // Remove null IDs
                if (dietIds.length === 0) return;

                // For standard diet preferences (not Mixed)
                if (dietIds.length === 1) {
                    $.ajax({
                        url: "{{ route('admin.getMealsByPreference') }}",
                        method: "GET",
                        data: {
                            diet_preference: dietIds[0]
                        },
                        success: function(data) {
                            populateMealSelectors(index, data);
                        },
                        error: function() {
                            toastr.error('Failed to load meals for diet preference', 'Error', {
                                timeOut: 3000,
                                progressBar: true,
                                positionClass: "toast-top-right"
                            });
                        }
                    });
                }
                // For Mixed diet (combine Veg and Non-Veg)
                else {
                    let promises = dietIds.map(dietId => {
                        return $.ajax({
                            url: "{{ route('admin.getMealsByPreference') }}",
                            method: "GET",
                            data: {
                                diet_preference: dietId
                            }
                        });
                    });

                    $.when.apply($, promises).then(function() {
                        let combinedData = {
                            1: [], // Breakfast
                            2: [], // Lunch
                            3: [] // Dinner
                        };

                        // Combine meals from all diet preferences
                        for (let i = 0; i < arguments.length; i++) {
                            const data = arguments[i][0];
                            if (data[1]) combinedData[1] = combinedData[1].concat(data[1]);
                            if (data[2]) combinedData[2] = combinedData[2].concat(data[2]);
                            if (data[3]) combinedData[3] = combinedData[3].concat(data[3]);
                        }

                        // Remove duplicates
                        combinedData[1] = removeDuplicates(combinedData[1]);
                        combinedData[2] = removeDuplicates(combinedData[2]);
                        combinedData[3] = removeDuplicates(combinedData[3]);

                        populateMealSelectors(index, combinedData);
                    }, function() {
                        toastr.error('Failed to load mixed meals', 'Error', {
                            timeOut: 3000,
                            progressBar: true,
                            positionClass: "toast-top-right"
                        });
                    });
                }
            }

            // Helper function to populate meal selectors
            function populateMealSelectors(index, data) {
                const group = $('.meal-section-group').eq(index);

                // Clear existing options
                group.find('.meal_title').empty();

                // Populate breakfast
                if (data[1] && data[1].length > 0) {
                    const breakfastSelect = group.find(`select[name="meal[${index}][breakfast][]"]`);
                    $.each(data[1], function(_, meal) {
                        const newOption = new Option(meal.title, meal.id, false, false);
                        breakfastSelect.append(newOption);
                    });
                    breakfastSelect.trigger('change');
                }

                // Populate lunch
                if (data[2] && data[2].length > 0) {
                    const lunchSelect = group.find(`select[name="meal[${index}][lunch][]"]`);
                    $.each(data[2], function(_, meal) {
                        const newOption = new Option(meal.title, meal.id, false, false);
                        lunchSelect.append(newOption);
                    });
                    lunchSelect.trigger('change');
                }

                // Populate dinner
                if (data[3] && data[3].length > 0) {
                    const dinnerSelect = group.find(`select[name="meal[${index}][dinner][]"]`);
                    $.each(data[3], function(_, meal) {
                        const newOption = new Option(meal.title, meal.id, false, false);
                        dinnerSelect.append(newOption);
                    });
                    dinnerSelect.trigger('change');
                }
            }

            // Helper function to remove duplicate meals
            function removeDuplicates(meals) {
                const unique = [];
                const ids = new Set();

                meals.forEach(meal => {
                    if (!ids.has(meal.id)) {
                        ids.add(meal.id);
                        unique.push(meal);
                    }
                });

                return unique;
            }

            // Your existing form validation remains the same
            $('form').on('submit', function(e) {
                let isValid = true;
                let errorMessages = [];

                $('.meal-section-group').each(function(index) {
                    const hasBreakfast = $(this).find('select[name*="[breakfast]"]').val() &&
                        $(this).find('select[name*="[breakfast]"]').val().length > 0;
                    const hasLunch = $(this).find('select[name*="[lunch]"]').val() &&
                        $(this).find('select[name*="[lunch]"]').val().length > 0;
                    const hasDinner = $(this).find('select[name*="[dinner]"]').val() &&
                        $(this).find('select[name*="[dinner]"]').val().length > 0;

                    if (!hasBreakfast && !hasLunch && !hasDinner) {
                        isValid = false;
                    }
                });

                if (!isValid) {
                    e.preventDefault();
                    toastr.error('Please select at least one meal for each diet type', '', {
                        timeOut: 5000,
                        progressBar: true,
                        closeButton: true,
                        positionClass: "toast-top-right"
                    });

                    $('html, body').animate({
                        scrollTop: $('.meal-section-group').first().offset().top - 100
                    }, 500);
                }
            });

        });
    </script>
@endpush
