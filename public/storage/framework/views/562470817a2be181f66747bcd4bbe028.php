<?php
    use App\Models\Utility;
    $settings_data = \App\Models\Utility::settingsById($bill->created_by);
?>
<!DOCTYPE html>
<html lang="en" dir="<?php echo e($settings_data['SITE_RTL'] == 'on' ? 'rtl' : ''); ?>">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link
        href="https://fonts.googleapis.com/css2?family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900&display=swap"
        rel="stylesheet">


    <style type="text/css">
        :root {
            --theme-color: <?php echo e($color); ?>;
            --white: #ffffff;
            --black: #000000;
        }

        body {
            font-family: 'Lato', sans-serif;
            page-break-inside: avoid;
        }

        p,
        li,
        ul,
        ol {
            margin: 0;
            padding: 0;
            list-style: none;
            line-height: 1.5;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table tr th {
            padding: 0.75rem;
            text-align: left;
        }

        table tr td {
            padding: 0.75rem;
            text-align: left;
        }

        table th small {
            display: block;
            font-size: 12px;
        }

        .bill-preview-main {
            max-width: 700px;
            width: 100%;
            margin: 0 auto;
            background: #ffff;
            box-shadow: 0 0 10px #ddd;
        }

        .bill-logo {
            max-width: 200px;
            width: 100%;
        }

        .bill-header table td {
            padding: 15px 30px;
        }

        .text-right {
            text-align: right;
        }

        .no-space tr td {
            padding: 0;
        }

        .vertical-align-top td {
            vertical-align: top;
        }

        .view-qrcode {
            max-width: 114px;
            height: 114px;
            margin-left: auto;
            margin-top: 15px;
            background: var(--white);
        }

        .view-qrcode img {
            width: 100%;
            height: 100%;
        }

        .bill-body {
            padding: 30px 25px 0;
        }

        table.add-border tr {
            border-top: 1px solid var(--theme-color);
        }

        tfoot tr:first-of-type {
            border-bottom: 1px solid var(--theme-color);
        }

        .total-table tr:first-of-type td {
            padding-top: 0;
        }

        .total-table tr:first-of-type {
            border-top: 0;
        }

        .sub-total {
            padding-right: 0;
            padding-left: 0;
        }

        .border-0 {
            border: none !important;
        }

        .bill-summary td,
        .bill-summary th {
            font-size: 13px;
            font-weight: 600;
        }

        .total-table td:last-of-type {
            width: 146px;
        }

        .bill-footer {
            padding: 15px 20px;
        }

        .itm-description td {
            padding-top: 0;
        }

        html[dir="rtl"] table tr td,
        html[dir="rtl"] table tr th {
            text-align: right;
        }

        html[dir="rtl"] .text-right {
            text-align: left;
        }

        html[dir="rtl"] .view-qrcode {
            margin-left: 0;
            margin-right: auto;
        }

        p:not(:last-of-type) {
            margin-bottom: 15px;
        }

        .bill-summary p {
            margin-bottom: 0;
        }
    </style>

    <?php if(env('SITE_RTL') == 'on'): ?>
        <link rel="stylesheet" href="<?php echo e(asset('css/bootstrap-rtl.css')); ?>">
    <?php endif; ?>
</head>

<body>
    <div class="bill-preview-main" id="boxes">
        <div class="bill-header">
            <table>
                <tbody>
                    <tr style="border-bottom:1px solid var(--theme-color);">
                        <td>
                            <img class="bill-logo" src="<?php echo e($img); ?>" alt="">
                        </td>
                        <td class="text-right">
                            <h3
                                style="text-transform: uppercase; font-size: 40px; font-weight: bold; color: var(--theme-color);">
                                <?php echo e(__('BILL')); ?></h3>
                        </td>
                    </tr>
                </tbody>
            </table>
            <table class="vertical-align-top">
                <tbody>
                    <tr>
                        <td>
                            <p>
                                <?php if($settings['company_name']): ?>
                                    <?php echo e($settings['company_name']); ?>

                                <?php endif; ?>
                                <br>
                                <?php if($settings['company_email']): ?>
                                    <?php echo e($settings['company_email']); ?>

                                <?php endif; ?>
                                <br>
                                <?php if($settings['company_telephone']): ?>
                                    <?php echo e($settings['company_telephone']); ?>

                                <?php endif; ?>
                                <br>
                                <?php if($settings['company_address']): ?>
                                    <?php echo e($settings['company_address']); ?>

                                <?php endif; ?>
                                <?php if($settings['company_city']): ?>
                                    <br> <?php echo e($settings['company_city']); ?>,
                                <?php endif; ?>
                                <?php if($settings['company_state']): ?>
                                    <?php echo e($settings['company_state']); ?>

                                <?php endif; ?>
                                <?php if($settings['company_country']): ?>
                                    <br><?php echo e($settings['company_country']); ?>

                                <?php endif; ?>
                                <?php if($settings['company_zipcode']): ?>
                                    - <?php echo e($settings['company_zipcode']); ?>

                                <?php endif; ?>
                                <br>
                                <?php if(!empty($settings['registration_number'])): ?>
                                    <?php echo e(__('Registration Number')); ?> : <?php echo e($settings['registration_number']); ?>

                                <?php endif; ?>

                                <?php if(App\Models\Utility::getValByName('tax_number') == 'on'): ?>
                                    <?php if(!empty($settings['tax_type']) && !empty($settings['vat_number'])): ?><br>
                                        <?php echo e($settings['tax_type'] . ' ' . __('Number')); ?> : <?php echo e($settings['vat_number']); ?>

                                        <br>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </p>
                        </td>
                        <td>
                            <table class="no-space">
                                <tbody>
                                <?php if(isset($settings_data['bill_qr_display']) && $settings_data['bill_qr_display'] == 'on'): ?>
                                    <tr>
                                        <td colspan="2">
                                            <div class="view-qrcode" style="margin-top: 0; margin-bottom: 15px;">
                                                <?php echo DNS2D::getBarcodeHTML(route('pay.billpay', \Crypt::encrypt($bill->bill_id)), 'QRCODE', 2, 2); ?>

                                            </div>
                                        </td>
                                    </tr>
                                    <?php endif; ?>
                                    <tr>
                                        <td><?php echo e(__('Number')); ?>:</td>
                                        <td class="text-right">
                                            <?php echo e(Utility::billNumberFormat($settings, $bill->bill_id)); ?></td>
                                    </tr>
                                    <tr>
                                        <td><?php echo e(__('Issue Date')); ?>:</td>
                                        <td class="text-right"><?php echo e(Utility::dateFormat($settings, $bill->issue_date)); ?>

                                        </td>
                                    </tr>
                                    <tr>
                                        <td><?php echo e(__('Due Date')); ?>:</td>
                                        <td class="text-right"><?php echo e(Utility::dateFormat($settings, $bill->due_date)); ?>

                                        </td>
                                    </tr>
                                    <?php if(!empty($customFields) && count($bill->customField) > 0): ?>
                                        <?php $__currentLoopData = $customFields; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <tr>
                                                <td><?php echo e($field->name); ?> :</td>
                                                <td> <?php echo e(!empty($bill->customField) ? $bill->customField[$field->id] : '-'); ?>

                                                </td>
                                            </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    <?php endif; ?>

                                </tbody>
                            </table>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="bill-body">
            <table>
                <tbody>
                    <tr>
                        <td>
                            <strong style="margin-bottom: 10px; display:block;"><?php echo e(__('Bill To')); ?>:</strong>
                            <p>
                                <?php echo e(!empty($vendor->billing_name)?$vendor->billing_name:''); ?><br>
                                <?php echo e(!empty($vendor->billing_address)?$vendor->billing_address:''); ?><br>
                                <?php echo e(!empty($vendor->billing_city)?$vendor->billing_city:'' .', '); ?>, <?php echo e(!empty($vendor->billing_state)?$vendor->billing_state:'',', '); ?> <?php echo e(!empty($vendor->billing_zip)?$vendor->billing_zip:''); ?><br>
                                <?php echo e(!empty($vendor->billing_country)?$vendor->billing_country:''); ?><br>
                                <?php echo e(!empty($vendor->billing_phone)?$vendor->billing_phone:''); ?><br>
                            </p>
                        </td>
                        <?php if($settings['shipping_display'] == 'on'): ?>
                            <td class="text-right">
                                <strong style="margin-bottom: 10px; display:block;"><?php echo e(__('Ship To')); ?>:</strong>
                                <p>
                                    <?php echo e(!empty($vendor->shipping_name)?$vendor->shipping_name:''); ?><br>
                                    <?php echo e(!empty($vendor->shipping_address)?$vendor->shipping_address:''); ?><br>
                                    <?php echo e(!empty($vendor->shipping_city)?$vendor->shipping_city:'' . ', '); ?>, <?php echo e(!empty($vendor->shipping_state)?$vendor->shipping_state:'' .', '); ?> <?php echo e(!empty($vendor->shipping_zip)?$vendor->shipping_zip:''); ?><br>
                                    <?php echo e(!empty($vendor->shipping_country)?$vendor->shipping_country:''); ?><br>
                                    <?php echo e(!empty($vendor->shipping_phone)?$vendor->shipping_phone:''); ?><br>
                                </p>
                            </td>
                        <?php endif; ?>
                    </tr>
                </tbody>
            </table>
            <table class="add-border bill-summary" style="margin-top: 30px;">
                <thead style="background-color: var(--theme-color);color: var(--white);">
                    <tr>
                        <th><?php echo e(__('Item')); ?></th>
                        <th><?php echo e(__('Quantity')); ?></th>
                        <th><?php echo e(__('Rate')); ?></th>
                        <th><?php echo e(__('Discount')); ?></th>
                        <th><?php echo e(__('Tax')); ?> (%)</th>
                        <th><?php echo e(__('Price')); ?> <small><?php echo e(__('after tax & discount')); ?></small></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(isset($bill->itemData) && count($bill->itemData) > 0): ?>
                        <?php $__currentLoopData = $bill->itemData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td><?php echo e($item->name); ?></td>
                                <td><?php echo e($item->quantity); ?></td>
                                <td><?php echo e(Utility::priceFormat($settings, $item->price)); ?></td>
                                <td><?php echo e($item->discount != 0 ? Utility::priceFormat($settings, $item->discount) : '-'); ?></td>
                                <td>
                                    <?php if(!empty($item->itemTax)): ?>
                                        <?php
                                            $itemtax = 0;
                                        ?>
                                        <?php $__currentLoopData = $item->itemTax; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $taxes): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <?php
                                                $itemtax += $taxes['tax_price'];
                                            ?>
                                            <p><?php echo e($taxes['name']); ?> (<?php echo e($taxes['rate']); ?>) <?php echo e($taxes['price']); ?></p>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    <?php else: ?>
                                        <span>-</span>
                                    <?php endif; ?>
                                </td>
                                <?php
                                    $itemtax = 0;
                                ?>
                                <td><?php echo e(Utility::priceFormat($settings, $item->price * $item->quantity - $item->discount + $itemtax)); ?>

                                </td>
                                <?php if(!empty($item->description)): ?>
                            <tr class="border-0 itm-description">
                                <td colspan="6" style="border-bottom:1px solid <?php echo e($color); ?>;">
                                    <?php echo e($item->description); ?></td>
                            </tr>
                        <?php endif; ?>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php else: ?>
                    <?php endif; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td><?php echo e(__('Total')); ?></td>
                        <td><?php echo e($bill->totalQuantity); ?></td>
                        <td><?php echo e(Utility::priceFormat($settings, $bill->totalRate)); ?></td>
                        <td><?php echo e(Utility::priceFormat($settings, $bill->totalDiscount)); ?></td>
                        <td><?php echo e(Utility::priceFormat($settings, $bill->totalTaxPrice)); ?></td>
                        <td><?php echo e(Utility::priceFormat($settings, $bill->getSubTotal())); ?></td>
                    </tr>
                    <tr>
                        <td colspan="4"></td>
                        <td colspan="2" class="sub-total">
                            <table class="total-table">
                                <tr>
                                    <td><?php echo e(__('Subtotal')); ?>:</td>
                                    <td><?php echo e(Utility::priceFormat($settings, $bill->getSubTotal())); ?></td>
                                </tr>
                                <tr>
                                    <td><?php echo e(__('Discount')); ?>:</td>
                                    <td><?php echo e(Utility::priceFormat($settings, $bill->getTotalDiscount())); ?></td>
                                </tr>
                                <?php if(!empty($bill->taxesData)): ?>
                                    <?php $__currentLoopData = $bill->taxesData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $taxName => $taxPrice): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <td><?php echo e($taxName); ?> :</td>
                                            <td><?php echo e(Utility::priceFormat($settings, $taxPrice)); ?></td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <?php endif; ?>
                                <tr>
                                    <td><?php echo e(__('Total')); ?>:</td>
                                    <td><?php echo e(Utility::priceFormat($settings, $bill->getSubTotal() - $bill->getTotalDiscount() + $bill->getTotalTax())); ?>

                                    </td>
                                </tr>
                                <tr>
                                    <td><?php echo e(__('Paid')); ?>:</td>
                                    <td><?php echo e(Utility::priceFormat($settings, $bill->getTotal() - $bill->getDue() - $bill->billTotalDebitNote())); ?>

                                    </td>
                                </tr>
                                <tr>
                                    <td><?php echo e(__('Debit Note')); ?>:</td>
                                    <td><?php echo e(Utility::priceFormat($settings, $bill->billTotalDebitNote())); ?></td>
                                </tr>
                                <tr>
                                    <td><?php echo e(__('Amount Due')); ?>:</td>
                                    <td><?php echo e(Utility::priceFormat($settings, $bill->getDue())); ?></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </tfoot>
            </table>
            <div class="bill-footer">
                <p>
                    <?php echo e($settings['footer_title']); ?> <br>
                    <?php echo $settings['footer_notes']; ?>

                </p>
            </div>
        </div>
    </div>


    <?php if(!isset($preview)): ?>
        <?php echo $__env->make('bill.script', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>;
    <?php endif; ?>

</body>

</html>
<?php /**PATH /var/www/vhosts/ardan.com.do/account.ardan.com.do/resources/views/bill/templates/template8.blade.php ENDPATH**/ ?>