<?php $__env->startSection('page-title'); ?>
    <?php if(\Auth::user()->type == 'company'): ?>
        <?php echo e(__('Manage Email Templates')); ?>

    <?php else: ?>
        <?php echo e(__('Manage Email Templates')); ?>

    <?php endif; ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>"><?php echo e(__('Dashboard')); ?></a></li>
    <?php if(\Auth::user()->type == 'company'): ?>
        <li class="breadcrumb-item active" aria-current="page"><?php echo e(__('Email Templates')); ?></li>
    <?php else: ?>
        <li class="breadcrumb-item active" aria-current="page"><?php echo e(__('Email Templates')); ?></li>
    <?php endif; ?>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('script-page'); ?>
    <script type="text/javascript">
        $(document).on("click", ".email-template-checkbox", function() {
            var chbox = $(this);
            $.ajax({
                url: chbox.attr('data-url'),
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    status: chbox.val()
                },
                type: 'post',
                success: function(response) {
                    if (response.is_success) {
                        toastr('Success', response.success, 'success');
                        if (chbox.val() == 1) {
                            $('#' + chbox.attr('id')).val(0);
                        } else {
                            $('#' + chbox.attr('id')).val(1);
                        }
                    } else {
                        toastr('Error', response.error, 'error');
                    }
                },
                error: function(response) {
                    response = response.responseJSON;
                    if (response.is_success) {
                        toastr('Error', response.error, 'error');
                    } else {
                        toastr('Error', response, 'error');
                    }
                }
            })
        });
    </script>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header card-body table-border-style">
                    <h5></h5>
                    <div class="table-responsive">
                        <table class="table datatable" id="pc-dt-simple">
                            <thead>
                                <tr>
                                    <th scope="col" class="sort" data-sort="name"> <?php echo e(__('Name')); ?></th>
                                    <th class="text-end"><?php echo e(__('Action')); ?></th>
                                   
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $EmailTemplates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $EmailTemplate): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td><?php echo e($EmailTemplate->name); ?></td>
                                        <td>
                                            <div class="dt-buttons">
                                                <?php if(\Auth::user()->type == 'super admin'): ?>
                                                    <div class="text-end">
                                                        <div class="action-btn bg-warning">
                                                            <a href="<?php echo e(route('manage.email.language', [$EmailTemplate->id, \Auth::user()->lang])); ?>"
                                                                class="mx-3 btn btn-sm d-inline-flex align-items-center"
                                                                data-bs-toggle="tooltip" title="<?php echo e(__('View')); ?>">
                                                                <span class="text-white"><i class="ti ti-eye"></i></span>
                                                            </a>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                                <?php if(\Auth::user()->type == 'company'): ?>
                                                    <div class="text-end">
                                                    <div class="action-btn ">
                                                            <a href="<?php echo e(route('manage.email.language', [$EmailTemplate->id, \Auth::user()->lang])); ?>"
                                                                class="mx-3 btn btn-sm d-inline-flex align-items-center bg-warning"
                                                                data-bs-toggle="tooltip" title="<?php echo e(__('View')); ?>">
                                                                <span class="text-white"><i class="ti ti-eye"></i></span>
                                                            </a>
                                                        </div>

                                
                                                    </div>
                                                <?php endif; ?>
                                            </div>
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
<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/vhosts/ardan.com.do/account.ardan.com.do/resources/views/email_templates/index.blade.php ENDPATH**/ ?>