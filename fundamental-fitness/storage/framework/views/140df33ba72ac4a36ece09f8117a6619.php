<?php $__env->startSection('content'); ?>
<?php $__env->startSection('admin-title', 'Edit Workout Settings'); ?>

<?php $__env->startSection('content'); ?>
    <div class="container-fluid">
        <div class="pate-content-wrapper">
            <div class="page-title-row">
                <h5>Select</h5>
            </div>
            <div class="m-card-min-hight">
                <form method="POST" action="<?php echo e(route('admin.workoutSettingsUpdate', $workoutProgramId)); ?>">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('POST'); ?>

                    <div class="row">
                        <?php $__currentLoopData = $questions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $question): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="col-md-6 mb-3">
                                <?php
                                    $rawTitle = trim($question->title_for_web); // remove extra spaces
                                    $hasAsterisk = substr($rawTitle, -1) === '*'; // check if ends with *
                                    $title = $hasAsterisk ? rtrim($rawTitle, '*') : $rawTitle; // remove trailing *
                                ?>
                                <label for="question_<?php echo e($question->id); ?>" class="text-14 font-500">
                                    <?php echo e($title); ?> 
                                    <?php if($hasAsterisk): ?>
                                        <span class="text-danger" style="color:red">*</span>
                                    <?php endif; ?>
                                </label>
                                <div class="checkbox-bg">
                                    <?php $__currentLoopData = $question->options; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input"
                                                type="<?php echo e($question->type_for_web == 2 ? 'checkbox' : 'radio'); ?>"
                                                id="option_<?php echo e($option->id); ?>"
                                                name="responses[<?php echo e($question->id); ?>]<?php echo e($question->type_for_web == 2 ? '[]' : ''); ?>"
                                                value="<?php echo e($option->id); ?>"
                                                <?php $saved = $savedResponses[$question->id] ?? [];
                                            $isChecked = ($question->type_for_web == 2) 
                                            ? in_array($option->id, $saved) 
                                            : (count($saved) && $saved[0] == $option->id); ?>
                                                <?php echo e($isChecked ? 'checked' : ''); ?>

                                                <?php echo e($question->type_for_web == 1 ? 'required' : ''); ?>>
                                            <label class="form-check-label" for="option_<?php echo e($option->id); ?>">
                                                <?php echo e($option->label_for_web); ?>

                                            </label>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>

                                <?php $__errorArgs = ["responses.{$question->id}"];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="text-danger"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
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
                            <a href="<?php echo e(route('admin.workoutPlansEdit', $workoutProgramId)); ?>" class="btn btn-outline-primary btn-sm me-2">Back</a>
                            <button type="submit" name="action" value="next" class="btn btn-primary btn-sm">Next</button>
                            
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

<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layout.index', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/app.fundamental-fit.co.uk/releases/20251124_065248/resources/views/admin/workout-plan/workout-settings-edit.blade.php ENDPATH**/ ?>