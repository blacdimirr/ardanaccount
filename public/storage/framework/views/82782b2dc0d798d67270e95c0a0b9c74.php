<?php
use App\Models\Utility;
$setting_arr = Utility::file_validate();
?>



<!-- [ Main Content ] end -->
<footer class="dash-footer">
    <div class="footer-wrapper">
        <div class="py-1">
            <span class="text-muted">
                <?php echo e(__('©')); ?> <?php echo e(date('Y')); ?> <?php echo e(Utility::getValByName('footer_text') ? Utility::getValByName('footer_text') : config('app.name', 'WorkGo')); ?>

            </span>
        </div>
        <div class="py-1">
    
    
    
    
    
    
    
    
    
    
    
    
    
    
        </div>
    </div>
</footer>



<!-- Warning Section Ends -->
<!-- Required Js -->
<script src="<?php echo e(asset('js/jquery.min.js')); ?>"></script>
<script src="<?php echo e(asset('js/jquery.form.js')); ?>"></script>
<script src="<?php echo e(asset('assets/js/plugins/popper.min.js')); ?>"></script>
<script src="<?php echo e(asset('assets/js/plugins/perfect-scrollbar.min.js')); ?>"></script>
<script src="<?php echo e(asset('assets/js/plugins/bootstrap.min.js')); ?>"></script>
<script src="<?php echo e(asset('assets/js/plugins/feather.min.js')); ?>"></script>
<script src="<?php echo e(asset('assets/js/dash.js')); ?>"></script>

<script src="<?php echo e(asset('assets/js/plugins/datepicker-full.min.js')); ?>"></script>
<script src="<?php echo e(asset('assets/js/plugins/dropzone-amd-module.min.js')); ?>"></script>

<script src="<?php echo e(asset('assets/js/plugins/choices.min.js')); ?>"></script>

<script src="<?php echo e(asset('assets/js/plugins/sweetalert2.all.min.js')); ?>"></script>
<script src="<?php echo e(asset('assets/js/plugins/simple-datatables.js')); ?>"></script>

<!-- sweet alert Js -->



<!--Botstrap switch-->
<script src="<?php echo e(asset('assets/js/plugins/bootstrap-switch-button.min.js')); ?>"></script>


<!-- Apex Chart -->
<script src="<?php echo e(asset('assets/js/plugins/apexcharts.min.js')); ?>"></script>
<script src="<?php echo e(asset('assets/js/plugins/main.min.js')); ?>"></script>
<script src="<?php echo e(asset('assets/js/plugins/flatpickr.min.js')); ?>"></script>

<script>
    var file_size = "<?php echo e($setting_arr['max_size']); ?>";
    var file_types = "<?php echo e($setting_arr['types']); ?>";
    var type_err = "<?php echo e(__('Invalid file type. Please select a valid file ')); ?>" + "(<?php echo e($setting_arr['types']); ?>).";
    var size_err = "<?php echo e(__('File size exceeds the maximum limit of ')); ?>" + " <?php echo e($setting_arr['max_size'] / 1024); ?> MB.";
</script>

<script src="<?php echo e(asset('js/custom.js')); ?>"></script>

<script src="<?php echo e(asset('assets/js/plugins/simplebar.min.js')); ?>"></script>

<?php if($message = Session::get('success')): ?>
    <script>
        show_toastr('success', '<?php echo $message; ?>');
    </script>
<?php endif; ?>
<?php if($message = Session::get('error')): ?>
    <script>
        show_toastr('error', '<?php echo $message; ?>');
    </script>
<?php endif; ?>
<?php echo $__env->yieldPushContent('script-page'); ?>



<script>

    feather.replace();

    function removeClassByPrefix(node, prefix) {
        for (let i = 0; i < node.classList.length; i++) {
            let value = node.classList[i];
            if (value.startsWith(prefix)) {
                node.classList.remove(value);
            }
        }
    }
</script>
<?php /**PATH C:\Users\willt\Downloads\account\account.ardan.com.do\resources\views/partials/admin/footer.blade.php ENDPATH**/ ?>