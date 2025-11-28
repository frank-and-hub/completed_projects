<?php $__env->startSection('admin-title', 'Edit Meal Plan'); ?>

<?php $__env->startPush('styles'); ?>
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
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
    <div class="container-fluid d-flex justify-content-center align-items-center" style="min-height: 90vh; background: #f5f5f5;">
        <div class="card shadow-lg p-4" style="border-radius: 18px; width: 100%; background: #fff;">
            <h3 class="mb-4" style="font-weight: 600;">Edit Meal Plan</h3>
            <form action="<?php echo e(route('admin.updateWorkoutMeal', $programId)); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <?php echo method_field('POST'); ?>

                <div id="meal-sections-wrapper">
                    <?php
                        $requiredDietPreferences = ['Veg', 'Vegan', 'Keto', 'Mixed'];
                        $currentIndex = 0;
                        $dietPrefIdsMap = [];

                        foreach ($mealDietPreferences as $pref) {
                            $dietPrefIdsMap[strtolower($pref->name)] = $pref->id;
                        }
                    ?>

                    <?php $__currentLoopData = $requiredDietPreferences; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dietPreference): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $dietKey = strtolower($dietPreference);
                            $targetDietIds = [];

                            if ($dietKey === 'mixed') {
                                $targetDietIds = [
                                    $dietPrefIdsMap['mixed'] ?? null
                                ];
                            } elseif (isset($dietPrefIdsMap[$dietKey])) {
                                $targetDietIds = [ $dietPrefIdsMap[$dietKey] ];
                            }

                            $targetDietIds = array_filter($targetDietIds);
                            if (empty($targetDietIds)) continue;

                            $breakfastMeals = [];
                            $lunchMeals = [];
                            $dinnerMeals = [];

                            foreach ($targetDietIds as $id) {
                                $breakfastMeals = array_merge($breakfastMeals, $groupedMeals[$id]['breakfast'] ?? []);
                                $lunchMeals = array_merge($lunchMeals, $groupedMeals[$id]['lunch'] ?? []);
                                $dinnerMeals = array_merge($dinnerMeals, $groupedMeals[$id]['dinner'] ?? []);
                            }

                            $unique = fn($array) => array_values(array_unique($array));
                            $breakfastMeals = $unique($breakfastMeals);
                            $lunchMeals = $unique($lunchMeals);
                            $dinnerMeals = $unique($dinnerMeals);

                            // pree($breakfastMeals);
                        ?>

                        <div class="meal-section-group mb-4 p-3" style="background: #fcf6f5; border-radius: 12px;">
                            <div class="diet-type-title"><?php echo e($dietPreference); ?> Meals</div>

                            <input type="hidden" name="meal[<?php echo e($currentIndex); ?>][diet_preference]" value="<?php echo e(implode(',', $targetDietIds)); ?>">

                            <div class="meal-section mb-4">
                                <label class="fw-bold">Breakfast</label>
                                <select class="form-control meal_title" name="meal[<?php echo e($currentIndex); ?>][breakfast][]" multiple>
                                    <?php $__currentLoopData = $breakfastMeals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mealId): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php if(isset($allMeals[$mealId])): ?>
                                            <option value="<?php echo e($mealId); ?>" selected><?php echo e($allMeals[$mealId]->title); ?></option>
                                        <?php endif; ?>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>

                            <div class="meal-section mb-4">
                                <label class="fw-bold">Lunch</label>
                                <select class="form-control meal_title" name="meal[<?php echo e($currentIndex); ?>][lunch][]" multiple>
                                    <?php $__currentLoopData = $lunchMeals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mealId): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php if(isset($allMeals[$mealId])): ?>
                                            <option value="<?php echo e($mealId); ?>" selected><?php echo e($allMeals[$mealId]->title); ?></option>
                                        <?php endif; ?>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>

                            <div class="meal-section mb-4">
                                <label class="fw-bold">Dinner</label>
                                <select class="form-control meal_title" name="meal[<?php echo e($currentIndex); ?>][dinner][]" multiple>
                                    <?php $__currentLoopData = $dinnerMeals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mealId): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php if(isset($allMeals[$mealId])): ?>
                                            <option value="<?php echo e($mealId); ?>" selected><?php echo e($allMeals[$mealId]->title); ?></option>
                                        <?php endif; ?>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                        </div>
                        <?php $currentIndex++; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>

                <div class="mt-4 d-flex justify-content-end">
                    <a href="<?php echo e(route('admin.workoutSettingsEdit', $programId)); ?>" class="btn btn-outline-primary btn-sm me-2">Back</a>
                    <button type="submit" class="btn btn-primary btn-sm">Update Meal Plan</button>
                </div>
            </form>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('script'); ?>
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
            <?php $__currentLoopData = $requiredDietPreferences; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $dietPreference): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    if(strtolower($dietPreference) == 'mixed') {
                        // For Mixed, we'll use both Veg and Non-Veg IDs
                        $dietPrefIds = [2, 3]; // vegId, non -veg

                    } else {
                        $dietPrefId = null;
                        foreach($mealDietPreferences as $pref) {
                            if(strtolower($pref->name) == strtolower($dietPreference)) {
                                $dietPrefId = $pref->id;
                                break;
                            }
                        }
                        $dietPrefIds = [$dietPrefId];
                    }
                ?>
                
                <?php if(!empty(array_filter($dietPrefIds))): ?>
                    loadMealsForDietPreference(<?php echo e($index); ?>, [<?php $__currentLoopData = $dietPrefIds; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $id): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php echo e($id); ?><?php if(!$loop->last): ?>, <?php endif; ?> <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>]);
                <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

            // Function to load meals for a specific diet preference (or multiple preferences)
            function loadMealsForDietPreference(index, dietIds) {
                dietIds = dietIds.filter(id => id != null); // Remove null IDs
                if (dietIds.length === 0) return;

                // For standard diet preferences (not Mixed)
                if (dietIds.length === 1) {
                    $.ajax({
                        url: "<?php echo e(route('admin.getMealsByPreference')); ?>",
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
                            url: "<?php echo e(route('admin.getMealsByPreference')); ?>",
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
                            3: []  // Dinner
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
            // function populateMealSelectors(index, data) {
            //     const group = $('.meal-section-group').eq(index);
                
            //     // Clear existing options
            //     group.find('.meal_title').empty();
                
            //     // Populate breakfast
            //     if (data[1] && data[1].length > 0) {
            //         const breakfastSelect = group.find(`select[name="meal[${index}][breakfast][]"]`);
            //         $.each(data[1], function(_, meal) {
            //             const newOption = new Option(meal.title, meal.id, false, false);
            //             breakfastSelect.append(newOption);
            //         });
            //         breakfastSelect.trigger('change');
            //     }
                
            //     // Populate lunch
            //     if (data[2] && data[2].length > 0) {
            //         const lunchSelect = group.find(`select[name="meal[${index}][lunch][]"]`);
            //         $.each(data[2], function(_, meal) {
            //             const newOption = new Option(meal.title, meal.id, false, false);
            //             lunchSelect.append(newOption);
            //         });
            //         lunchSelect.trigger('change');
            //     }
                
            //     // Populate dinner
            //     if (data[3] && data[3].length > 0) {
            //         const dinnerSelect = group.find(`select[name="meal[${index}][dinner][]"]`);
            //         $.each(data[3], function(_, meal) {
            //             const newOption = new Option(meal.title, meal.id, false, false);
            //             dinnerSelect.append(newOption);
            //         });
            //         dinnerSelect.trigger('change');
            //     }
            // }

            function populateMealSelectors(index, data) {
    const group = $('.meal-section-group').eq(index);

    const types = {
        1: 'breakfast',
        2: 'lunch',
        3: 'dinner'
    };

    Object.entries(types).forEach(([typeKey, mealType]) => {
        const select = group.find(`select[name="meal[${index}][${mealType}][]"]`);
        if (!select.length || !data[typeKey]) return;

        data[typeKey].forEach(meal => {
            // Only add if not already present (avoids duplicates)
            if (select.find(`option[value="${meal.id}"]`).length === 0) {
                const option = new Option(meal.title, meal.id, false, false);
                select.append(option);
            }
        });

        select.trigger('change');
    });
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
            // $('form').on('submit', function(e) {
            //     let isValid = true;
            //     let errorMessages = [];
                
            //     $('.meal-section-group').each(function(index) {
            //         const hasBreakfast = $(this).find('select[name*="[breakfast]"]').val() && 
            //                             $(this).find('select[name*="[breakfast]"]').val().length > 0;
            //         const hasLunch = $(this).find('select[name*="[lunch]"]').val() && 
            //                         $(this).find('select[name*="[lunch]"]').val().length > 0;
            //         const hasDinner = $(this).find('select[name*="[dinner]"]').val() && 
            //                         $(this).find('select[name*="[dinner]"]').val().length > 0;
                    
            //         const dietType = $(this).find('.diet-type-title').text().replace(' Meals', '');
                    
            //         if (!hasBreakfast && !hasLunch && !hasDinner) {
            //             errorMessages.push(`Please select at least one meal for ${dietType}`);
            //             isValid = false;
            //         }
            //     });
                
            //     if (!isValid) {
            //         e.preventDefault();
            //         errorMessages = [...new Set(errorMessages)];
            //         errorMessages.forEach(msg => {
            //             toastr.error(msg, '', {
            //                 timeOut: 5000,
            //                 progressBar: true,
            //                 closeButton: true,
            //                 positionClass: "toast-top-right"
            //             });
            //         });
                    
            //         $('html, body').animate({
            //             scrollTop: $('.meal-section-group').first().offset().top - 100
            //         }, 500);
            //     }
            // });
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
<?php $__env->stopPush(); ?>

<?php echo $__env->make('admin.layout.index', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/app.fundamental-fit.co.uk/releases/20251124_065248/resources/views/admin/workout-plan/edit-meal.blade.php ENDPATH**/ ?>