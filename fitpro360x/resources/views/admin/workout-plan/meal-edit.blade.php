@extends('admin.layout.index')

@section('admin-title', 'Edit Meal')

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet" />
    
    <style>
        /* All existing styles remain */
        .card {
            border: none;
        }
        /* ... (keep other styles) ... */
        
        /* New styles */
        .diet-type-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 2px solid #ff7043;
            color: #333;
        }
        .diet-section {
            background: #fcf6f5;
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .is-invalid {
            border-color: #dc3545 !important;
        }
        .invalid-feedback {
            color: #dc3545;
            font-size: 0.875em;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid d-flex justify-content-center align-items-center" style="min-height: 90vh; background: #f5f5f5;">
        <div class="card shadow-lg p-4" style="border-radius: 18px; min-width: 900px; background: #fff;">
            <h3 class="mb-4" style="font-weight: 600;">Edit Meals</h3>
            <form id="mealForm" action="{{ route('admin.updateWorkoutMeal', $programId) }}" method="POST">
                @csrf
                @method('POST')

                @if ($errors->any())
                    <div class="alert alert-danger mb-4">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Meal Sections Wrapper -->
                <div id="meal-sections-wrapper">
                    @php
                        $dietTypes = [
                            'Veg' => $mealDietPreferences->firstWhere('name', 'Veg')->id ?? null,
                            'Vegan' => $mealDietPreferences->firstWhere('name', 'Vegan')->id ?? null,
                            'Keto' => $mealDietPreferences->firstWhere('name', 'Keto')->id ?? null,
                            'Mixed' => $mealDietPreferences->firstWhere('name', 'Mixed')->id ?? null
                        ];
                        
                        // Group existing meals by diet preference
                        $existingMeals = [];
                        foreach ($groupedMeals as $mealGroup) {
                            if ($dietPref = $mealDietPreferences->firstWhere('id', $mealGroup['diet_preference'])) {
                                $existingMeals[$dietPref->name] = $mealGroup;
                            }
                        }
                    @endphp
                    
                    @foreach($dietTypes as $dietName => $dietId)
                        @php
                            $index = $loop->index;
                            $mealData = $existingMeals[$dietName] ?? [
                                'breakfast' => [],
                                'lunch' => [],
                                'dinner' => [],
                                'diet_preference' => $dietId
                            ];
                        @endphp
                        
                        <div class="diet-section">
                            <div class="diet-type-title">{{ $dietName }} Meals</div>
                            <input type="hidden" name="meal[{{ $index }}][diet_preference]" value="{{ $dietId }}">
                            
                            <div class="meal-section mb-3">
                                <label class="fw-bold">Breakfast</label>
                                <select class="form-control meal_title" name="meal[{{ $index }}][breakfast][]" multiple>
                                    @foreach ($mealData['breakfast'] as $selected)
                                        <option value="{{ $selected }}" selected>{{ $selected }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback">Please select at least one breakfast option</div>
                            </div>
                            
                            <div class="meal-section mb-3">
                                <label class="fw-bold">Lunch</label>
                                <select class="form-control meal_title" name="meal[{{ $index }}][lunch][]" multiple>
                                    @foreach ($mealData['lunch'] as $selected)
                                        <option value="{{ $selected }}" selected>{{ $selected }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback">Please select at least one lunch option</div>
                            </div>
                            
                            <div class="meal-section mb-3">
                                <label class="fw-bold">Dinner</label>
                                <select class="form-control meal_title" name="meal[{{ $index }}][dinner][]" multiple>
                                    @foreach ($mealData['dinner'] as $selected)
                                        <option value="{{ $selected }}" selected>{{ $selected }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback">Please select at least one dinner option</div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-4 d-flex justify-content-end">
                    <a href="{{ route('admin.workoutSettingsEdit', $programId) }}" class="btn btn-outline-primary btn-sm me-2">Back</a>
                    <button type="submit" class="btn btn-primary btn-sm">Update Meals</button>
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
            // Initialize Select2
            $('.meal_title').select2({
                placeholder: "Select meal(s)",
                width: '100%',
                closeOnSelect: false
            });

            // Load meals for all diet types
            function loadAllMeals() {
                $('.diet-section').each(function() {
                    const dietId = $(this).find('input[name*="diet_preference"]').val();
                    const dietName = $(this).find('.diet-type-title').text().replace(' Meals', '');
                    
                    $(this).find('.meal-section').each(function() {
                        const mealType = $(this).find('label').text().trim();
                        const $select = $(this).find('select');
                        
                        if (dietId) {
                            loadMealsForSection($select, dietId, mealType);
                        }
                    });
                });
            }

            // Load meals for a specific section
            function loadMealsForSection($select, dietId, mealType) {
                $.ajax({
                    url: "{{ route('admin.getMealsByPreference') }}",
                    method: "GET",
                    data: {
                        diet_preference: dietId,
                        type: mealType.toLowerCase()
                    },
                    beforeSend: function() {
                        $select.prop('disabled', true);
                    },
                    success: function(data) {
                        if (data && data.meals) {
                            const currentValues = $select.val() || [];
                            
                            $select.empty();
                            data.meals.forEach(function(meal) {
                                const isSelected = currentValues.includes(meal.id.toString()) || 
                                                  currentValues.includes(meal.title);
                                const option = new Option(meal.title, meal.id, isSelected, isSelected);
                                $select.append(option);
                            });
                            
                            $select.trigger('change');
                        }
                    },
                    error: function() {
                        toastr.error('Failed to load meals', 'Error');
                    },
                    complete: function() {
                        $select.prop('disabled', false);
                    }
                });
            }

            // Form validation
            $('#mealForm').on('submit', function(e) {
                $('.is-invalid').removeClass('is-invalid');
                let isValid = true;
                let errorMessages = [];

                // Validate each section has at least one meal
                $('.diet-section').each(function() {
                    const dietName = $(this).find('.diet-type-title').text().replace(' Meals', '');
                    let sectionValid = true;
                    
                    $(this).find('.meal_title').each(function() {
                        if (!$(this).val() || $(this).val().length === 0) {
                            $(this).addClass('is-invalid');
                            sectionValid = false;
                        }
                    });

                    if (!sectionValid) {
                        errorMessages.push(`${dietName} section must have at least one meal selected in each category`);
                        isValid = false;
                    }
                });

                if (!isValid) {
                    e.preventDefault();
                    errorMessages.forEach(msg => {
                        toastr.error(msg, '', {timeOut: 5000});
                    });
                    
                    // Scroll to first error
                    $('html, body').animate({
                        scrollTop: $('.is-invalid').first().offset().top - 100
                    }, 500);
                }
            });

            // Initialize on page load
            loadAllMeals();
        });
    </script>
@endpush