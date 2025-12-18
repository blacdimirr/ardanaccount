<?php
    use App\Models\Utility;
    $logo = asset(Storage::url('uploads/logo/'));
    $company_logo = Utility::getValByName('company_logo');
    $settings = Utility::settings();

?>

<?php $__env->startSection('page-title'); ?>
    <?php echo e(__('Login')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('auth-lang'); ?>
<?php
    $languages = App\Models\Utility::languages();
?>
    <div class="lang-dropdown-only-desk">
        <li class="dropdown dash-h-item drp-language">
            <a class="dash-head-link dropdown-toggle btn" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                <span class="drp-text"> <?php echo e(ucFirst($languages[$lang])); ?>

                </span>
            </a>
            <div class="dropdown-menu dash-h-dropdown dropdown-menu-end">
                <?php $__currentLoopData = $languages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $code => $language): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a href="<?php echo e(route('login',$code)); ?>" tabindex="0" class="dropdown-item <?php echo e($code == $lang ? 'active':''); ?>">
                        <span><?php echo e(ucFirst($language)); ?></span>
                    </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </li>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="">
        <h2 class="mb-3 f-w-600"><?php echo e(__('Login')); ?></h2>
    </div>
    
    <?php echo e(Form::open(['route' => 'login', 'method' => 'post', 'id' => 'loginForm', 'class'=>'needs-validation','novalidate' ])); ?>

    <?php echo csrf_field(); ?>
    <?php if(session('status')): ?>
        <div class="mb-4 font-medium text-lg text-green-600 text-danger">
            <?php echo e(session('status')); ?>

        </div>
    <?php endif; ?>
    <div class="">
        <div class="form-group mb-3">
            <label for="email" class="form-label"><?php echo e(__('Email')); ?></label>
            <input class="form-control <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="email" type="email" name="email"
                value="<?php echo e(old('email')); ?>" required autocomplete="email" autofocus>
            <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <div class="invalid-feedback" role="alert"><?php echo e($message); ?></div>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>
        <div class="form-group mb-3">
            <label for="password" class="form-label"><?php echo e(__('Password')); ?></label>
            <input class="form-control <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="password" type="password"
                name="password" required autocomplete="current-password">
            <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <div class="invalid-feedback" role="alert"><?php echo e($message); ?></div>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

        </div>

        

        <?php if($settings['recaptcha_module'] == 'yes'): ?>
            <?php if(isset($settings['google_recaptcha_version']) && $settings['google_recaptcha_version'] == 'v2-checkbox'): ?>
                <div class="form-group mb-4">
                    <?php echo NoCaptcha::display($settings['cust_darklayout'] == 'on' ? ['data-theme' => 'dark'] : []); ?>

                    <?php $__errorArgs = ['g-recaptcha-response'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <span class="small text-danger" role="alert">
                            <strong><?php echo e($message); ?></strong>
                        </span>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
            <?php else: ?>
                <div class="form-group mb-4">
                    <input type="hidden" id="g-recaptcha-response" name="g-recaptcha-response" class="form-control">
                    <?php $__errorArgs = ['g-recaptcha-response'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <span class="error small text-danger" role="alert">
                            <strong><?php echo e($message); ?></strong>
                        </span>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>


        <div class="form-group mb-4">
            <?php if(Route::has('password.request')): ?>
                <a href="<?php echo e(route('password.request', $lang)); ?>" class="text-xs"><?php echo e(__('Forgot Your Password?')); ?></a>
            <?php endif; ?>
        </div>
        <div class="d-grid">
            <button type="submit" class="btn btn-primary btn-block mt-2" id="login_button"><?php echo e(__('Login')); ?></button>
        </div>

        <div class="row">
            <div class="col-6 d-grid">
                <a href="<?php echo e(route('customer.login')); ?>"
                    class="btn-login btn btn-secondary btn-block mt-2 text-white"><?php echo e(__('Customer Login')); ?></a>
            </div>
            <div class="col-6 text-end d-grid">
                <a href="<?php echo e(route('vender.login')); ?>"
                    class="btn-login btn btn-secondary btn-block mt-2 text-white"><?php echo e(__('Vendor Login')); ?></a>
            </div>
        </div>

    </div>
    <?php echo e(Form::close()); ?>

<?php $__env->stopSection(); ?>



<?php $__env->startPush('custom-scripts'); ?>
    
    <?php if(isset($settings['recaptcha_module']) && $settings['recaptcha_module'] == 'yes'): ?>
        <?php if(isset($settings['google_recaptcha_version']) && $settings['google_recaptcha_version'] == 'v2-checkbox'): ?>
            <?php echo NoCaptcha::renderJs(); ?>

        <?php else: ?>
            <script src="https://www.google.com/recaptcha/api.js?render=<?php echo e($settings['google_recaptcha_key']); ?>"></script>
            <script>
                $(document).ready(function() {
                    grecaptcha.ready(function() {
                        grecaptcha.execute('<?php echo e($settings['google_recaptcha_key']); ?>', {
                            action: 'submit'
                        }).then(function(token) {
                            $('#g-recaptcha-response').val(token);
                        });
                    });
                });
            </script>
        <?php endif; ?>
    <?php endif; ?>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.auth', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\willt\Downloads\account\account.ardan.com.do\resources\views/auth/login.blade.php ENDPATH**/ ?>