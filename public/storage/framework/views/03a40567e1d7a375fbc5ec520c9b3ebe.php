<?php $__env->startSection('page-title'); ?>
    <?php echo e(__('Manage Product-Service & Income-Expense Category')); ?>

<?php $__env->stopSection(); ?>
<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>"><?php echo e(__('Dashboard')); ?></a></li>
    <li class="breadcrumb-item"><?php echo e(__('Category')); ?></li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('action-btn'); ?>
    <div class="d-flex">
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create constant category')): ?>
            <a href="#" data-url="<?php echo e(route('product-category.create')); ?>" data-ajax-popup="true" data-bs-toggle="tooltip"
                title="<?php echo e(__('Create')); ?>" title="<?php echo e(__('Create')); ?>" data-title="<?php echo e(__('Create New Category')); ?>"
                class="btn btn-sm btn-primary">
                <i class="ti ti-plus"></i>
            </a>
        <?php endif; ?>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table datatable">
                            <thead>
                                <tr>
                                    <th> <?php echo e(__('Category')); ?></th>
                                    <th> <?php echo e(__('Type')); ?></th>
                                    <th> <?php echo e(__('Account')); ?></th>
                                    <th width="10%"> <?php echo e(__('Action')); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td class="font-style"><?php echo e($category->name); ?></td>
                                        <td class="font-style">
                                            <?php echo e(array_key_exists($category->type, \App\Models\ProductServiceCategory::$catTypes) ? __(\App\Models\ProductServiceCategory::$catTypes[$category->type]) : 'Unknown Type'); ?>

                                        </td>
                                        <td><?php echo e(!empty($category->chartAccount) ? $category->chartAccount->name : '-'); ?></td>
                                        <td class="Action">
                                            <span>
                                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('edit constant category')): ?>
                                                    <div class="action-btn me-2">
                                                        <a href="#" class="mx-3 btn btn-sm align-items-center bg-info"
                                                            data-url="<?php echo e(route('product-category.edit', $category->id)); ?>"
                                                            data-ajax-popup="true"
                                                            data-title="<?php echo e(__('Edit Product Category')); ?>"
                                                            data-bs-toggle="tooltip" title="<?php echo e(__('Edit')); ?>"
                                                            data-original-title="<?php echo e(__('Edit')); ?>">
                                                            <i class="ti ti-pencil text-white"></i>
                                                        </a>
                                                    </div>
                                                <?php endif; ?>
                                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete constant category')): ?>
                                                    <div class="action-btn">
                                                        <?php echo Form::open([
                                                            'method' => 'DELETE',
                                                            'route' => ['product-category.destroy', $category->id],
                                                            'id' => 'delete-form-' . $category->id,
                                                        ]); ?>

                                                        <a href="#"
                                                            class="mx-3 btn btn-sm align-items-center bs-pass-para bg-danger"
                                                            data-bs-toggle="tooltip" title="<?php echo e(__('Delete')); ?>"
                                                            data-original-title="<?php echo e(__('Delete')); ?>"
                                                            data-confirm="<?php echo e(__('Are You Sure?') . '|' . __('This action can not be undone. Do you want to continue?')); ?>"
                                                            data-confirm-yes="document.getElementById('delete-form-<?php echo e($category->id); ?>').submit();">
                                                            <i class="ti ti-trash text-white"></i>
                                                        </a>
                                                        <?php echo Form::close(); ?>

                                                    </div>
                                                <?php endif; ?>
                                            </span>
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

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/vhosts/ardan.com.do/account.ardan.com.do/resources/views/productServiceCategory/index.blade.php ENDPATH**/ ?>