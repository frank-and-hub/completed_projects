<?php $__env->startSection('content'); ?>
<?php $__env->startSection('admin-title', 'Workout Settings'); ?>

<?php $__env->startSection('content'); ?>
    <div class="container-fluid">
        <div class="pate-content-wrapper">
            <div class="page-title-row">
                <h5>Select</h5>
            </div>
            <div class="m-card-min-hight">
                <form method="POST" action="<?php echo e(route('admin.workoutupdate', $programId)); ?>">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('POST'); ?>

                    <div class="row">
                        <?php $__currentLoopData = $questions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $question): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $rawTitle = trim($question->title_for_web); // remove extra spaces
                                $hasAsterisk = substr($rawTitle, -1) === '*'; // check if ends with *
                                $title = $hasAsterisk ? rtrim($rawTitle, '*') : $rawTitle; // remove trailing *
                            ?>
                            <div class="col-md-6 mb-3">
                                <div class="question-wrapper" data-question-id="<?php echo e($question->id); ?>">
                                    <label><?php echo e($title); ?> <?php if($hasAsterisk): ?>
                                            <span class="text-danger">*</span>
                                        <?php endif; ?>
                                    </label>
                                    <div class="checkbox-bg">
                                        <?php $__currentLoopData = $question->options; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input"
                                                    type="<?php echo e($question->type_for_web == 2 ? 'checkbox' : 'radio'); ?>"
                                                    name="responses[<?php echo e($question->id); ?>]<?php echo e($question->type_for_web == 2 ? '[]' : ''); ?>"
                                                    value="<?php echo e($option->id); ?>">
                                                <label class="form-check-label"><?php echo e($option->label_for_web); ?></label>
                                            </div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>
                                    <div class="error-message text-danger" style="display:none;"></div>
                                </div>

                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                        <?php if($errors->any()): ?>
                            <div class="alert alert-danger">
                                <ul>
                                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <li><?php echo e($error); ?></li>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <div class="text-end mb-3">
                            <a href="<?php echo e(route('admin.workoutPlansEdit', $programId)); ?>"
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
            border-radius: 5px;
        }
    </style>

    <?php $__env->startPush('script'); ?>
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
    <?php $__env->stopPush(); ?>


<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layout.index', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/app.fundamental-fit.co.uk/releases/20251124_065248/resources/views/admin/workout-plan/workout-settings-add.blade.php ENDPATH**/ ?>