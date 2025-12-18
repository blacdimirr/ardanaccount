<?php
    $profile=asset(Storage::url('uploads/avatar/'));
?>
<?php $__env->startSection('page-title'); ?>
    <?php echo e(__('Manage User')); ?>

<?php $__env->stopSection(); ?>
<?php $__env->startPush('script-page'); ?>

<?php $__env->stopPush(); ?>
<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>"><?php echo e(__('Dashboard')); ?></a></li>
    <li class="breadcrumb-item"><?php echo e(__('User')); ?></li>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('action-btn'); ?>
    <div class="d-flex">
        <a href="#" data-size="md" data-url="<?php echo e(route('users.create')); ?>" data-ajax-popup="true"  data-bs-toggle="tooltip" title="<?php echo e(__('Create New User')); ?>"  class="btn btn-sm btn-primary me-2">
            <i class="ti ti-plus"></i>
        </a>
        <?php if(\Auth::user()->type == 'company'): ?>    
            <a href="<?php echo e(route('userlogs.index')); ?>" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="<?php echo e(__('User Log')); ?>">
                <i class="ti ti-user-check"></i>
            </a>
        <?php endif; ?>
    </div>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
    <div class="row">
        <div class="col-xxl-12">
            <div class="row">

                <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-header border-0 pb-0">
                                <div class="d-flex align-items-center" data-bs-toggle="tooltip" title="<?php echo e(__('Last Login')); ?>">
                                    <?php echo e((!empty($user->last_login_at)) ? $user->last_login_at : ''); ?>

                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0">
                                        <div class="badge p-2 px-3 bg-primary"><?php echo e($user->type); ?></div>
                                    </h6>
                                </div>
                                <?php if(Gate::check('edit user') || Gate::check('delete user')): ?>
                                    <div class="card-header-right">
                                        <div class="btn-group card-option">
                                        <?php if($user->is_active==1): ?>
                                            <button type="button" class="btn dropdown-toggle"
                                                    data-bs-toggle="dropdown" aria-haspopup="true"
                                                    aria-expanded="false">
                                                <i class="ti ti-dots-vertical"></i>
                                            </button>

                                            <div class="dropdown-menu dropdown-menu-end">

                                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('edit user')): ?>
                                                        <a href="#!" data-size="md" data-url="<?php echo e(route('users.edit',$user->id)); ?>" data-ajax-popup="true" class="dropdown-item" data-bs-original-title="<?php echo e(__('Edit User')); ?>">
                                                            <i class="ti ti-pencil"></i>
                                                            <span><?php echo e(__('Edit')); ?></span>
                                                        </a>
                                                <?php endif; ?>

                                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete user')): ?>
                                                    <?php echo Form::open(['method' => 'DELETE', 'route' => ['users.destroy', $user['id']],'id'=>'delete-form-'.$user['id']]); ?>

                                                    <a href="#!"  class="dropdown-item bs-pass-para">
                                                        <i class="ti ti-archive"></i>
                                                        <span> <?php if($user->delete_status!=0): ?><?php echo e(__('Delete')); ?> <?php else: ?> <?php echo e(__('Restore')); ?><?php endif; ?></span>
                                                    </a>

                                                    <?php echo Form::close(); ?>

                                                <?php endif; ?>

                                                <a href="#!" data-url="<?php echo e(route('users.reset',\Crypt::encrypt($user->id))); ?>" data-ajax-popup="true" data-size="md" class="dropdown-item" data-bs-original-title="<?php echo e(__('Reset Password')); ?>">
                                                    <i class="ti ti-adjustments"></i>
                                                    <span>  <?php echo e(__('Reset Password')); ?></span>
                                                </a>

                                                <?php if($user->is_enable_login == 1): ?>
                                                    <a href="<?php echo e(route('users.login', \Crypt::encrypt($user->id))); ?>"
                                                        class="dropdown-item">
                                                        <i class="ti ti-road-sign"></i>
                                                        <span class="text-danger"> <?php echo e(__('Login Disable')); ?></span>
                                                    </a>
                                                <?php elseif($user->is_enable_login == 0 && $user->password == null): ?>
                                                    <a href="#" data-url="<?php echo e(route('users.reset', \Crypt::encrypt($user->id))); ?>"
                                                        data-ajax-popup="true" data-size="md" class="dropdown-item login_enable"
                                                        data-title="<?php echo e(__('New Password')); ?>" class="dropdown-item">
                                                        <i class="ti ti-road-sign"></i>
                                                        <span class="text-success"> <?php echo e(__('Login Enable')); ?></span>
                                                    </a>
                                                <?php else: ?>
                                                    <a href="<?php echo e(route('users.login', \Crypt::encrypt($user->id))); ?>"
                                                        class="dropdown-item">
                                                        <i class="ti ti-road-sign"></i>
                                                        <span class="text-success"> <?php echo e(__('Login Enable')); ?></span>
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                            <?php else: ?>
                                                <a href="#" class="action-item"><i class="ti ti-lock"></i></a>
                                            <?php endif; ?>

                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="card-body">
                                <div class="avatar me-3">
                                    <img src="<?php echo e((!empty($user->avatar))? asset(Storage::url($user->avatar)): asset(Storage::url('uploads/avatar/avatar.png'))); ?>" alt="kal" class="img-fluid rounded border-2 border border-primary" width="120px" height="120px">
                                </div>
                                <h4 class=" mt-3"><?php echo e($user->name); ?></h4>
                                <small><?php echo e($user->email); ?></small>

                               
                            </div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                <div class="col-md-3">
                    <a href="#" class="btn-addnew-project border-primary"  data-ajax-popup="true" data-size="md" data-title="<?php echo e(__('Create New User')); ?>" data-url="<?php echo e(route('users.create')); ?>">
                        <div class="badge bg-primary proj-add-icon">
                            <i class="ti ti-plus"></i>
                        </div>
                        <h6 class="mt-4 mb-2"><?php echo e(__('New User')); ?></h6>
                        <p class="text-muted text-center"><?php echo e(__('Click here to add New User')); ?></p>
                    </a>
                </div>
 
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('script-page'); ?>
    <script>
        $(document).on('change', '#password_switch', function() {
            if ($(this).is(':checked')) {
                $('.ps_div').removeClass('d-none');
                $('#password').attr("required", true);

            } else {
                $('.ps_div').addClass('d-none');
                $('#password').val(null);
                $('#password').removeAttr("required");
            }
        });
        $(document).on('click', '.login_enable', function() {
            setTimeout(function() {
                $('.modal-body').append($('<input>', {
                    type: 'hidden',
                    val: 'true',
                    name: 'login_enable'
                }));
            }, 2000);
        });
    </script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\laragon\www\accountgo\resources\views/user/index.blade.php ENDPATH**/ ?>