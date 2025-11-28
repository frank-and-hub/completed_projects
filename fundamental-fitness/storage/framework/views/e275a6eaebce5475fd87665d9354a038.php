<?php $__env->startSection('content'); ?>
<?php $__env->startSection('admin-title', 'Exercise'); ?>

<div class="container-fluid pt-3 bg">
    <div class="page-content-wrapper">
        <div class="page-title-row justify-content-between mb-3 gap-3 flex-wrap">
            <h4 class="">Exercise</h4>
            <div>
                <a href="<?php echo e(route('admin.exerciseAdd')); ?>" class="btn btn-sm btn-primary d-flex align-items-center gap-2">
                    <img src="<?php echo e(asset('assets/images/add-circle.svg')); ?>" alt="plus-icon" style="width:18px;">
                    Add Exercise
                </a>
            </div>
        </div>


            <!-- Filter Section -->
            <div class="filter-row-search">
                <form action="<?php echo e(route('admin.exerciseIndex')); ?>" method="GET" class="filter-row-search-box g-2 mb-3">
                    <div>
                        <input type="text" name="search" value="<?php echo e(request('search')); ?>" class="form-control"
                               placeholder="Search by exercise name">
                    </div>
                    <div>
                        <select name="frequency" class="form-select">
                            <option value="">All Workout Frequency</option>
                            <?php $__currentLoopData = $frequencies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $frequency): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($frequency->id); ?>" <?php echo e(request('frequency') == $frequency->id ? 'selected' : ''); ?>>
                                    <?php echo e($frequency->name); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>

                    <div>
                        <select name="meso" class="form-select">
                            <option value="">All Meso</option>
                            <?php $__currentLoopData = $mesos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $meso): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($meso->id); ?>" <?php echo e(request('meso') == $meso->id ? 'selected' : ''); ?>>
                                    <?php echo e($meso->name); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>

                    <div>
                        <select name="week" class="form-select">
                            <option value="">All Weeks</option>
                            <option value="1" <?php echo e(request('week') == 1 ? 'selected' : ''); ?>>Week 1</option>
                            <option value="2" <?php echo e(request('week') == 2 ? 'selected' : ''); ?>>Week 2</option>
                            <option value="3" <?php echo e(request('week') == 3 ? 'selected' : ''); ?>>Week 3</option>
                            <option value="4" <?php echo e(request('week') == 4 ? 'selected' : ''); ?>>Week 4</option>
                        </select>
                    </div>
                    <div>
                        <select name="day" class="form-select">
                            <option value="">All Days</option>
                            <option value="1" <?php echo e(request('day') == 1 ? 'selected' : ''); ?>>Day 1</option>
                            <option value="2" <?php echo e(request('day') == 2 ? 'selected' : ''); ?>>Day 2</option>
                            <option value="3" <?php echo e(request('day') == 3 ? 'selected' : ''); ?>>Day 3</option>
                            <option value="4" <?php echo e(request('day') == 4 ? 'selected' : ''); ?>>Day 4</option>
                            <option value="5" <?php echo e(request('day') == 5 ? 'selected' : ''); ?>>Day 5</option>
                            <option value="6" <?php echo e(request('day') == 6 ? 'selected' : ''); ?>>Day 6</option>
                        </select>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Search</button>
                        <a href="<?php echo e(route('admin.exerciseIndex')); ?>" class="btn btn-outline-secondary">Reset</a>
                    </div>
                </form>
            </div>

            <!-- Table Section -->
            <div class="table-responsive">
                <table class="table exercisetable" style="white-space: nowrap;">
                    <thead>
                        <tr>
                            <th>S.&nbsp;No.</th>
                            <th>Exercise Name</th>
                            <th>Workout Frequency</th>
                            <th>Meso</th>
                            <th>Week</th>
                            <th>Day</th>
                            <th>Level</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>

                        <?php $__empty_1 = true; $__currentLoopData = $exercises; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $workout): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td style="max-width: 50px; min-width: 10px; width: 50px"><?php echo e(($exercises->currentPage() - 1) * $exercises->perPage() + $loop->iteration); ?></td>
                            <td><?php echo e($workout->exercise->name ?? 'N/A'); ?></td>
                            <td><?php echo e($workout->workout_frequency->name ?? 'N/A'); ?></td>
                            <td><?php echo e($workout->meso->name ?? 'N/A'); ?></td>
                            <td><?php echo e($workout->week_id); ?></td>
                            <td><?php echo e($workout->day_id); ?></td>
                            <td>
                                <?php if($workout->level == 1): ?> Beginner
                                <?php elseif($workout->level == 2): ?> Intermediate
                                <?php else: ?> Advanced
                                <?php endif; ?>
                            </td>
                            <td class="text-center">

                                    <a href="<?php echo e(route('admin.exerciseEdit', $workout->id)); ?>">
                                        <img src="<?php echo e(asset('assets/images/edittbtn.svg')); ?>" alt="Edit" title="Edit">
                                    </a>
                                    <a href="javascript:void(0)" class="delete-muscle ms-2" data-id="<?php echo e($workout->id); ?>" data-page="<?php echo e(request()->get('page', 1)); ?>">
                                        <img src="<?php echo e(asset('assets/images/delete-circle-btn.svg')); ?>" alt="Delete" title="Delete">
                                    </a>

                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="13" class="text-center"><strong>No data found</strong></td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination Section -->
            <div class="d-flex flex-wrap gap-3 justify-content-between align-items-center mt-3 mb-5">
                <div class="d-flex align-items-center gap-2">
                    <select id="paginationLimit" class="form-select" style="width:90px;" onchange="window.location.href='?limit=' + this.value">
                        <option value="10" <?php echo e(request('limit',10) == 10 ? 'selected' : ''); ?>>10</option>
                        <option value="50" <?php echo e(request('limit') == 50 ? 'selected' : ''); ?>>50</option>
                        <option value="100" <?php echo e(request('limit') == 100 ? 'selected' : ''); ?>>100</option>
                    </select>
                    <p class="mb-0">
                        <?php if($exercises->total() > 0): ?>
                            Showing <?php echo e($exercises->firstItem()); ?> to <?php echo e($exercises->lastItem()); ?> of <?php echo e($exercises->total()); ?> entries
                        <?php else: ?>
                            No entries found
                        <?php endif; ?>
                    </p>
                </div>
                <div>
                    <?php echo e($exercises->links('pagination.custom')); ?>

                </div>
            </div>

    </div>
</div>
<?php $__env->stopSection(); ?>
<?php $__env->startPush('scripts'); ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
        const msg = localStorage.getItem('toastrMessage');
        if (msg) {
            toastr.success(msg);
            localStorage.removeItem('toastrMessage');
        }
    });
$(document).on("click", ".delete-muscle", function () {
    let id = $(this).data("id");
    let page = $(this).data("page");

    Swal.fire({
        title: "Are you sure?",
        text: "This will delete the exercise and all related data!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#3085d6",
        confirmButtonText: "Yes, delete it!"
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "<?php echo e(route('admin.exerciseDelete', '')); ?>/" + id,
                type: "DELETE",
                data: {
                    _token: "<?php echo e(csrf_token()); ?>",
                    page: page

                },
                success: function (res) {
                if (res.success) {
                    Swal.fire("Deleted!", res.message, "success").then(() => {
                        window.location.href = res.redirect_url;
                    });
                } else {
                    Swal.fire("Error!", res.message, "error");
                }
            },
            });
        }
    });
});

$(document).ready(function () {
    let message = localStorage.getItem('toastrMessage');
    let error = localStorage.getItem('toastrError');

    if (message) {
        toastr.success(message);
        localStorage.removeItem('toastrMessage');
    }

    if (error) {
        toastr.error(error);
        localStorage.removeItem('toastrError');
    }
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('admin.layout.index', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/app.fundamental-fit.co.uk/releases/20251124_065248/resources/views/admin/exercise/index.blade.php ENDPATH**/ ?>