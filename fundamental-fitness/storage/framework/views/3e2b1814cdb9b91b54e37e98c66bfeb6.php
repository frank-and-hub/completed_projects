<?php
    $index = $index ?? 0;
    $mealDietPreferences = $mealDietPreferences ?? [];
?>

<div class="meal-section-group mb-3 p-3" style="background: #fcf6f5; border-radius: 12px;">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div class="d-flex align-items-center gap-2">
            <label class="fw-bold me-2">Select Type:</label>
            <select name="meal[<?php echo e($index); ?>][diet_preference]"
                class="form-select d-inline-block w-auto  diet_preference_selector diet-preference"
                style="min-width: 160px; border-radius: 8px;">
                <option value="">Select diet preference</option>
                <?php $__currentLoopData = $mealDietPreferences; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mealDietPreference): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($mealDietPreference->id); ?>"><?php echo e($mealDietPreference->name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <button type="button" class="btn btn-outline-danger btn-sm remove-meal-group" style="border-radius: 8px;">
            Remove
        </button>
    </div>

    <div class="meal-section mb-4">
        <label class="fw-bold">Breakfast</label>
        <select class="form-control meal_title" name="meal[<?php echo e($index); ?>][breakfast][]" multiple></select>
    </div>

    <div class="meal-section mb-4">
        <label class="fw-bold">Lunch</label>
        <select class="form-control meal_title" name="meal[<?php echo e($index); ?>][lunch][]" multiple></select>
    </div>

    <div class="meal-section mb-4">
        <label class="fw-bold">Dinner</label>
        <select class="form-control meal_title" name="meal[<?php echo e($index); ?>][dinner][]" multiple></select>
    </div>
</div>
<?php /**PATH /var/www/app.fundamental-fit.co.uk/releases/20251124_065248/resources/views/components/admin-workout-meal.blade.php ENDPATH**/ ?>