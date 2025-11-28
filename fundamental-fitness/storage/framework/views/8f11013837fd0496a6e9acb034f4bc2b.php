<?php $__env->startSection('content'); ?>
<?php $__env->startSection('admin-title', 'Manage Meso'); ?>

<div class="container-fluid pt-3 bg">
    <div class="page-content-wrapper">
        <div class="page-title-row justify-content-between">
            <h4 class="mb-3 ms-3">Manage Meso</h4>
            
        </div>
        <div class="m-card-min-hight">
            <div class="filter-row-search justify-content-between">
                <form method="GET" action="<?php echo e(route('admin.mesoCycleIndex')); ?>" class="d-flex mb-2 align-items-center">
                    <div class="tp-search-input me-2" style="flex-grow: 1;">
                        <input type="text" class="form-control" name="search" value="<?php echo e(request('search')); ?>"
                            class="form-control icon-holder me-2" placeholder="Search with name">
                    </div>

                    <!-- Workout Frequency Filter -->
                    

                    <!-- Weeks Filter -->
                    <div class="tp-search-input me-2" style="flex-grow: 1;">
                        <select class="form-select" name="weeks" style="width:150px;">
                            <option value="">All Weeks</option>
                            <?php $__currentLoopData = \App\Constants\WeekConstants::Frequency['mesho_weeks']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $week): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($week); ?>" <?php echo e(request('weeks') == $week ? 'selected' : ''); ?>>
                                    <?php echo e($week); ?> <?php echo e($week > 1 ? 'Weeks' : 'Week'); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>


                    <button type="submit" class="btn btn-outline-primary search-btn mx-2">Search</button>
                    <a href="<?php echo e(route('admin.mesoCycleIndex')); ?>" class="btn btn-outline-secondary">Reset</a>
                </form>
            </div>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th style="width:5%">S. NO.</th>
                            <th style="width:20%">NAME</th>
                            
                            <th style="width:25%">WEEKS</th>
                            <th style="width:25%">EXERCISES</th>
                            <th style="width:25%">CREATED DATE</th>
                            
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $allMeso; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $meso): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><?php echo e(($allMeso->currentPage() - 1) * $allMeso->perPage() + $loop->iteration); ?></td>
                                <td><?php echo e($meso->name); ?></td>
                                
                                <td><?php echo e($meso->week_number); ?> <?php echo e($meso->week_number > 1 ? 'Weeks' : 'Week'); ?></td>

                                <td><?php echo e($meso->exercises ?? '10'); ?></td>
                                <td><?php echo e($meso->created_at->format('d/m/Y')); ?></td>
                                
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="7" class="text-center"><strong>No data found</strong></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="table-result mt-3">
                <select id="paginationLimit" class="form-select me-3" style="width:90px;">
                    <option value="10" <?php echo e(request('limit', 10) == 10 ? 'selected' : ''); ?>>10</option>
                    <option value="15" <?php echo e(request('limit') == 15 ? 'selected' : ''); ?>>15</option>
                    <option value="20" <?php echo e(request('limit') == 20 ? 'selected' : ''); ?>>20</option>
                </select>
                <p class="mb-0">
                    <?php if($allMeso->total() > 0): ?>
                        Showing <?php echo e($allMeso->firstItem()); ?> to <?php echo e($allMeso->lastItem()); ?> of <?php echo e($allMeso->total()); ?> entries
                    <?php else: ?>
                        No entries found
                    <?php endif; ?>
                </p>
                <nav class="ms-auto">
                    <?php echo e($allMeso->appends(request()->query())->links('pagination.custom')); ?>

                </nav>

            </div>
        </div>
    </div>
</div>

<!-- Add Meso Modal -->
<div class="modal fade" id="addMesoModal" tabindex="-1" aria-labelledby="addMesoModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addMesoModalLabel">Add Mesho</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addMesoForm">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="meso_id" id="meso_id" value="">

                    <div class="mb-3 mt-4">
                        <label for="mesoTitle" class="form-label">Title<span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="mesoTitle" name="meso_title" maxlength="50" placeholder="Enter meso title   ">
                        <span class="text-danger d-none" id="mesoTitleError">Meso Title is required</span>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Week</label>
                        <div class="d-flex gap-2 flex-wrap">
                            <?php $__currentLoopData = \App\Constants\WeekConstants::Frequency['mesho_weeks']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $week): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="week" id="week<?php echo e($week); ?>" value="<?php echo e($week); ?>" <?php echo e($week == 1 ? 'checked' : ''); ?>>
                                <label class="form-check-label" for="week<?php echo e($week); ?>">
                                    <?php echo e($week); ?> <?php echo e($week > 1 ? 'Weeks' : 'Week'); ?>

                                </label>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>

                </form>
            </div>
            <div class="justify-content-end d-flex mb-3">
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary"  data-bs-dismiss="modal" aria-label="Close">Close</button>
                    <button type="button" class="btn btn-primary" id="submitMesoForm">Submit</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center p-4">
                <input type="hidden" id="deleteMealsPlanId">
                <h5 class="mb-3">Are you sure you want to delete this meso?</h5>
                <button type="button" class="btn btn-outline-secondary me-2" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-outline-primary" id="confirmDelete">Delete</button>
            </div>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center p-4">
                <div class="mb-3">
                    <i class="fas fa-check-circle text-success" style="font-size: 3rem;"></i>
                </div>
                <h5 class="mb-3" id="successMessage"></h5>
                <button type="button" class="btn btn-primary" id="successModalOk">OK</button>
            </div>
        </div>
    </div>
</div>

<!-- Error Modal -->
<div class="modal fade" id="errorModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center p-4">
                <div class="mb-3">
                    <i class="fas fa-exclamation-circle text-danger" style="font-size: 3rem;"></i>
                </div>
                <h5 class="mb-3" id="errorMessage"></h5>
                <button type="button" class="btn btn-primary" id="errorModalOk">OK</button>
            </div>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<style>
    .action-icons {
        display: flex;
        gap: 10px;
    }
    .action-icons i {
        cursor: pointer;
        font-size: 18px;
    }
    .modal-content {
        border-radius: 10px;
        border: none;
    }
    .modal-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
        border-top-left-radius: 10px;
        border-top-right-radius: 10px;
    }
    .form-check {
        margin-bottom: 5px;
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    let deleteId = null;

    $(document).ready(function() {
        // Pagination limit change
        $('#paginationLimit').on('change', function() {
            let limit = $(this).val();
            let url = new URL(window.location.href);
            url.searchParams.set('limit', limit);
            url.searchParams.set('page', 1);
            window.location.href = url.toString();
        });

        // Reset form button
            $('#resetMesoForm').on('click', function() {
                $('#addMesoForm')[0].reset();
                $('.form-check-input[name="workout_frequency"]').first().prop('checked', true);
                $('.form-check-input[name="week"]').first().prop('checked', true);
                $('.text-danger').addClass('d-none');

                if ($('#meso_id').val()) {
                    $('#addMesoModalLabel').text('Edit Mesho');
                } else {
                    $('#addMesoModalLabel').text('Add Mesho');
                }
            });

        // Edit meso
        $('.edit-meso').on('click', function() {
            let id = $(this).data('id');

            $.ajax({
                url: "<?php echo e(route('admin.mesoCycleEdit', '')); ?>/" + id,
                method: "GET",
                success: function(response) {
                    $('#meso_id').val(id);
                    $('#mesoTitle').val(response.name);
                    // $(`input[name="workout_frequency"][value="${response.workout_frequency_id}"]`).prop('checked', true);
                    $(`input[name="week"][value="${response.week_number}"]`).prop('checked', true);
                    $('#addMesoModalLabel').text('Edit Mesho');
                    $('#addMesoModal').modal('show');
                },
                error: function(xhr) {
                    showError('Error loading meso data. Please try again.');
                }
            });
        });

        // Delete meso
        $('.delete-meso').on('click', function() {
            deleteId = $(this).data('id');
            $('#deleteConfirmModal').modal('show');
        });

        // Confirm delete
        $('#confirmDelete').on('click', function() {
            if (deleteId) {
                $.ajax({
                    url: "<?php echo e(route('admin.mesoCycleDelete', '')); ?>/" + deleteId,
                    method: "DELETE",
                    data: {
                        _token: "<?php echo e(csrf_token()); ?>"
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#deleteConfirmModal').modal('hide');
                            showSuccess(response.message);
                        } else {
                            showError(response.message || 'Something went wrong.');
                        }
                    },
                    error: function(xhr) {
                        showError('Error deleting meso. Please try again.');
                    }
                });
            }
        });

        // Submit form (add/edit)
        $('#submitMesoForm').on('click', function(e) {
            e.preventDefault();
            let isValid = true;

            let title = $('#mesoTitle').val().trim();
            // let workout_frequency = $('input[name="workout_frequency"]:checked').val();
            let week = $('input[name="week"]:checked').val();
            let meso_id = $('#meso_id').val();

            // Validation
            if (!title || title.length > 50) {
                $('#mesoTitleError').removeClass('d-none');
                isValid = false;
            } else {
                $('#mesoTitleError').addClass('d-none');
            }



            if (isValid) {
                let url = meso_id ? "<?php echo e(route('admin.mesoCycleUpdate', '')); ?>/" + meso_id : "<?php echo e(route('admin.mesoCycleSave')); ?>";
                let method = meso_id ? "POST" : "POST";

                $.ajax({
                    url: url,
                    method: method,
                    data: {
                        _token: "<?php echo e(csrf_token()); ?>",
                        meso_title: title,
                        // coach_notes: notes,
                        // workout_frequency: workout_frequency,
                        week: week
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#addMesoModal').modal('hide');
                            showSuccess(response.message);
                        } else {
                            showError(response.message || 'Something went wrong.');
                        }
                    },
                    error: function(xhr) {
                        let errorMessage = 'Error saving meso. Please try again.';
                        if (xhr.responseJSON && xhr.responseJSON.errors) {
                            errorMessage = '';
                            $.each(xhr.responseJSON.errors, function(key, value) {
                                errorMessage += value + '<br>';
                            });
                        }
                        showError(errorMessage);
                    }
                });
            }
        });

        // Success Modal OK button
        $('#successModalOk').on('click', function() {
            $('#successModal').modal('hide');
            location.reload();
        });

        // Error Modal OK button
        $('#errorModalOk').on('click', function() {
            $('#errorModal').modal('hide');
        });

        // Reset form on modal close
        $('#addMesoModal').on('hidden.bs.modal', function () {
            $('#addMesoForm')[0].reset();
            $('#meso_id').val('');
            // $('.form-check-input[name="workout_frequency"]').first().prop('checked', true);
            $('.form-check-input[name="week"]').first().prop('checked', true);
            $('.text-danger').addClass('d-none');
            $('#addMesoModalLabel').text('Add Mesho');
        });
    });

    function showSuccess(message) {
        $('#successMessage').html(message);
        $('#successModal').modal('show');
    }

    function showError(message) {
        $('#errorMessage').html(message);
        $('#errorModal').modal('show');
    }
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('admin.layout.index', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/app.fundamental-fit.co.uk/releases/20251124_065248/resources/views/admin/mesho-cycle/index.blade.php ENDPATH**/ ?>