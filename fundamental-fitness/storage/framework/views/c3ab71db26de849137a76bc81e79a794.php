<?php if($paginator->hasPages()): ?>
<ul class="pagination mb-0">

    
    <li class="page-item <?php echo e($paginator->onFirstPage() ? 'disabled' : ''); ?>">
        <a class="page-link" href="<?php echo e(preg_replace('/(\?|&)page=1$/', '', $paginator->url(1))); ?>" aria-label="First">
            <span aria-hidden="true">
                <img src="<?php echo e(asset('assets/images/first-page-icon.png')); ?>">
            </span>
        </a>
    </li>


    
    <li class="page-item <?php echo e($paginator->onFirstPage() ? 'disabled' : ''); ?>">
        <a class="page-link" href="<?php echo e($paginator->previousPageUrl()); ?>" aria-label="Previous">
            <span aria-hidden="true">
                <img src="<?php echo e(asset('assets/images/pre-page.png')); ?>">
            </span>
        </a>
    </li>

    
    <?php $__currentLoopData = $elements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $element): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php if(is_string($element)): ?>
            <li class="page-item disabled"><span class="page-link"><?php echo e($element); ?></span></li>
        <?php endif; ?>

        <?php if(is_array($element)): ?>
            <?php $__currentLoopData = $element; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page => $url): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    // Remove ?page=1 from the first page link
                    if ($page === 1) {
                        $url = preg_replace('/(\?|&)page=1$/', '', $url);
                    }
                ?>

                <li class="page-item <?php echo e($page == $paginator->currentPage() ? 'active' : ''); ?>">
                    <a class="page-link" href="<?php echo e($url); ?>"><?php echo e($page); ?></a>
                </li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <?php endif; ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

    
    <li class="page-item <?php echo e(!$paginator->hasMorePages() ? 'disabled' : ''); ?>">
        <a class="page-link" href="<?php echo e($paginator->nextPageUrl()); ?>" aria-label="Next">
            <span aria-hidden="true">
                <img src="<?php echo e(asset('assets/images/next-page-icon.png')); ?>">
            </span>
        </a>
    </li>

    
    <li class="page-item <?php echo e(!$paginator->hasMorePages() ? 'disabled' : ''); ?>">
        <a class="page-link" href="<?php echo e($paginator->url($paginator->lastPage())); ?>" aria-label="Last">
            <span aria-hidden="true">
                <img src="<?php echo e(asset('assets/images/last-page-icon.png')); ?>">
            </span>
        </a>
    </li>
</ul>
<?php endif; ?>
<?php /**PATH /var/www/app.fundamental-fit.co.uk/releases/20251124_065248/resources/views/pagination/custom.blade.php ENDPATH**/ ?>