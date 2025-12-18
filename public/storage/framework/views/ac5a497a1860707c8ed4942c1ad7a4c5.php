<form class="pl-3 pr-3" method="post" action="<?php echo e(route('test.send.mail')); ?>" id="test_email">
    <div class="modal-body">
    <?php echo csrf_field(); ?>
    <div class="form-group">
        <label for="email" class="col-form-label"><?php echo e(__('E-Mail Address')); ?></label>
        <input type="email" class="form-control" id="email" name="email" placeholder="<?php echo e(__('Please enter Email Address')); ?>" required/>
    </div>
    <div class="form-group">
        <input type="hidden" name="mail_driver" value="<?php echo e($data['mail_driver']); ?>" />
        <input type="hidden" name="mail_host" value="<?php echo e($data['mail_host']); ?>" />
        <input type="hidden" name="mail_port" value="<?php echo e($data['mail_port']); ?>" />
        <input type="hidden" name="mail_username" value="<?php echo e($data['mail_username']); ?>" />
        <input type="hidden" name="mail_password" value="<?php echo e($data['mail_password']); ?>" />
        <input type="hidden" name="mail_encryption" value="<?php echo e($data['mail_encryption']); ?>" />
        <input type="hidden" name="mail_from_address" value="<?php echo e($data['mail_from_address']); ?>" />
        <input type="hidden" name="mail_from_name" value="<?php echo e($data['mail_from_name']); ?>" />
        <button class="btn btn-primary float-end mb-3" type="submit"><?php echo e(__('Send Test Mail')); ?></button>
        <label id="email_sending" class="float-left" style="display: none;"><i class="ti ti-clock"></i> <?php echo e(__('Sending ...')); ?> </label>
    </div>
    </div>
</form>
<?php /**PATH /var/www/vhosts/ardan.com.do/account.ardan.com.do/resources/views/settings/test_mail.blade.php ENDPATH**/ ?>