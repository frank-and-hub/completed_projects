@php
    $index = $index ?? 0;
    $mealDietPreferences = $mealDietPreferences ?? [];
@endphp

<div class="meal-section-group mb-3 p-3" style="background: #fcf6f5; border-radius: 12px;">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div class="d-flex align-items-center gap-2">
            <label class="fw-bold me-2">Select Type:</label>
            <select name="meal[{{ $index }}][diet_preference]"
                class="form-select d-inline-block w-auto  diet_preference_selector diet-preference"
                style="min-width: 160px; border-radius: 8px;">
                <option value="">Select diet preference</option>
                @foreach ($mealDietPreferences as $mealDietPreference)
                    <option value="{{ $mealDietPreference->id }}">{{ $mealDietPreference->name }}</option>
                @endforeach
            </select>
        </div>
        <button type="button" class="btn btn-outline-danger btn-sm remove-meal-group" style="border-radius: 8px;">
            Remove
        </button>
    </div>

    <div class="meal-section mb-4">
        <label class="fw-bold">Breakfast</label>
        <select class="form-control meal_title" name="meal[{{ $index }}][breakfast][]" multiple></select>
    </div>

    <div class="meal-section mb-4">
        <label class="fw-bold">Lunch</label>
        <select class="form-control meal_title" name="meal[{{ $index }}][lunch][]" multiple></select>
    </div>

    <div class="meal-section mb-4">
        <label class="fw-bold">Dinner</label>
        <select class="form-control meal_title" name="meal[{{ $index }}][dinner][]" multiple></select>
    </div>
</div>
