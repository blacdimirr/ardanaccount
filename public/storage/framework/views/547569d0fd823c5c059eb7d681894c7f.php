<?php $__env->startSection('page-title'); ?>
    <?php if(\Auth::user()->type == 'company'): ?>
        <?php echo e(__('Manage Notification Templates')); ?>

    <?php endif; ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>"><?php echo e(__('Dashboard')); ?></a></li>
    <?php if(\Auth::user()->type == 'company'): ?>
        <li class="breadcrumb-item active" aria-current="page"><?php echo e(__('Notification Templates')); ?></li>
    <?php else: ?>
        <li class="breadcrumb-item active" aria-current="page"><?php echo e(__('Email Template')); ?></li>
    <?php endif; ?>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('action-btn'); ?>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
    <div class="">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header card-body table-border-style">
                    <h5></h5>
                    <div class="table-responsive">
                        <table class="table datatable" id="pc-dt-simple">
                            <thead>
                                <tr>
                                    <th scope="col" class="sort" data-sort="name"> <?php echo e(__('Name')); ?></th>
                                    <?php if(\Auth::user()->type == 'company'): ?>
                                        <th class="text-end"><?php echo e(__('Action')); ?></th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $notification_templates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notification_template): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td><?php echo e($notification_template->name); ?></td>
                                        <td>
                                            <?php if(\Auth::user()->type == 'company'): ?>
                                                <div class="text-end">
                                                    <div class="dt-buttons">
                                                        <span>
                                                    <div class="action-btn ">
                                                        <a href="<?php echo e(route('manage.notification.language', [$notification_template->id, \Auth::user()->lang])); ?>"
                                                            class="mx-3 btn btn-sm d-inline-flex align-items-center bg-warning"
                                                            data-bs-toggle="tooltip" data-bs-original-title="<?php echo e(__('View')); ?>" title="">
                                                            <span class="text-white"><i class="ti ti-eye"></i></span>
                                                        </a>
                                                    </div>
                                                        </span>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/vhosts/ardan.com.do/account.ardan.com.do/resources/views/notification_templates/index.blade.php ENDPATH**/ ?>