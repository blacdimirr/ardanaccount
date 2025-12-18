<?php $__env->startSection('page-title'); ?>
    <?php echo e(__('Bill Detail')); ?>

<?php $__env->stopSection(); ?>
<?php
use App\Models\Utility;
    $settings = App\Models\Utility::settings();
?>
<?php $__env->startPush('script-page'); ?>
    <script>
        $('.cp_link').on('click', function() {
            var value = $(this).attr('data-link');
            var $temp = $("<input>");
            $("body").append($temp);
            $temp.val(value).select();
            document.execCommand("copy");
            $temp.remove();
            show_toastr('success', '<?php echo e(__('Link Copy on Clipboard')); ?>', 'success')
        });

        $('.cp_link_document_auth').on('click', function() {
            var value = $(this).attr('data-link');
            var $temp = $("<input>");
            $("body").append($temp);
            $temp.val(value).select();
            document.execCommand("copy");
            $temp.remove();

            $.ajax({
                url: '<?php echo e(route('bill.sendEmailAuth', $bill->id)); ?>',
                type: 'POST',
                data: {
                    _token: '<?php echo e(csrf_token()); ?>'
                },
                success: function(data) {
                    if (data.success) {
                        show_toastr('success', '<?php echo e(__('The authorization document was sent')); ?>', 'success');
                    } else {
                        show_toastr('error', data.error, 'error');
                    }
                },
                error: function() {
                    show_toastr('error', '<?php echo e(__('Something went wrong')); ?>', 'error');
                }
            });
            
        });

$('.cp_link_document_auth_approved').on('click', function() {
            var value = $(this).attr('data-link');
            var $temp = $("<input>");
            $("body").append($temp);
            $temp.val(value).select();
            document.execCommand("copy");
            $temp.remove();

            $.ajax({
                url: '<?php echo e(route('bill.sendEmailAuthAproved', $bill->id)); ?>',
                type: 'POST',
                data: {
                    _token: '<?php echo e(csrf_token()); ?>'
                },
                success: function(data) {
                    if (data.success) {
                        show_toastr('success', '<?php echo e(__('The authorization document was sent')); ?>', 'success');
                    } else {
                        show_toastr('error', data.error, 'error');
                    }
                },
                error: function() {
                    show_toastr('error', '<?php echo e(__('Something went wrong')); ?>', 'error');
                }
            });
            
        });

    </script>
<?php $__env->stopPush(); ?>
<?php $__env->startSection('breadcrumb'); ?>
    <?php if(\Auth::guard('vender')->check()): ?>
        <li class="breadcrumb-item"><a href="<?php echo e(route('vender.dashboard')); ?>"><?php echo e(__('Dashboard')); ?></a></li>
    <?php else: ?>
        <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>"><?php echo e(__('Dashboard')); ?></a></li>
    <?php endif; ?>
    <?php if(\Auth::user()->type == 'company'): ?>
        <li class="breadcrumb-item"><a href="<?php echo e(route('bill.index')); ?>"><?php echo e(__('Bill')); ?></a></li>
    <?php else: ?>
        <li class="breadcrumb-item"><a href="<?php echo e(route('vender.bill')); ?>"><?php echo e(__('Bill')); ?></a></li>
    <?php endif; ?>

    <li class="breadcrumb-item"><?php echo e(Auth::user()->billNumberFormat($bill->bill_id)); ?></li>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('action-btn'); ?>
    <div class="float-end">
     <a href="#" class="btn btn-sm btn-info cp_link_document_auth"
            data-link="<?php echo e(route('pay.billpay', \Illuminate\Support\Facades\Crypt::encrypt($bill->id))); ?>"
            data-bs-toggle="tooltip" title="<?php echo e(__('enviar solicitud de transferencia')); ?>"
            data-original-title="<?php echo e(__('Click to send authorization document')); ?>">
            <span class="btn-inner--icon text-white"><i class="ti ti-files"></i></span>
        </a>
         <a href="#" class="btn btn-sm btn-info cp_link_document_auth_approved"
            data-link="<?php echo e(route('pay.billpay', \Illuminate\Support\Facades\Crypt::encrypt($bill->id))); ?>"
            data-bs-toggle="tooltip" title="<?php echo e(__('generar documento transferencia aprovada')); ?>"
            data-original-title="<?php echo e(__('Click to send document')); ?>">
            <span class="btn-inner--icon text-white"><i class="ti ti-receipt"></i></span>
        </a>
        <a href="#" class="btn btn-sm btn-primary cp_link"
            data-link="<?php echo e(route('pay.billpay', \Illuminate\Support\Facades\Crypt::encrypt($bill->id))); ?>"
            data-bs-toggle="tooltip" title="<?php echo e(__('copy bill')); ?>"
            data-original-title="<?php echo e(__('Click to copy invoice link')); ?>">
            <span class="btn-inner--icon text-white"><i class="ti ti-file"></i></span>
        </a>
    </div>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>

    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('send bill')): ?>
        <?php if($bill->status != 4): ?>
            <div class="row">
                <div class="card">
                    <div class="card-body">
                        <div class="row timeline-wrapper">
                            <div class="col-md-6 col-lg-4 col-xl-4">
                                <div class="timeline-icons"><span class="timeline-dots"></span>
                                    <i class="ti ti-plus text-primary"></i>
                                </div>
                                <h6 class="text-primary my-3"><?php echo e(__('Create Bill')); ?></h6>
                                <p class="text-muted text-sm mb-3"><i
                                        class="ti ti-clock mr-2"></i><?php echo e(__('Created on ')); ?><?php echo e(\Auth::user()->dateFormat($bill->bill_date)); ?>

                                </p>
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('edit bill')): ?>
                                    <a href="<?php echo e(route('bill.edit', \Crypt::encrypt($bill->id))); ?>" class="btn btn-sm btn-primary"
                                        data-bs-toggle="tooltip" data-original-title="<?php echo e(__('Edit')); ?>"><i
                                            class="ti ti-pencil mr-2"></i><?php echo e(__('Edit')); ?></a>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6 col-lg-4 col-xl-4">
                                <div class="timeline-icons"><span class="timeline-dots"></span>
                                    <i class="ti ti-mail text-warning"></i>
                                </div>
                                <h6 class="text-warning my-3"><?php echo e(__('Send Bill')); ?></h6>
                                <p class="text-muted text-sm mb-3">
                                    <?php if($bill->status != 0): ?>
                                        <i class="ti ti-clock mr-2"></i><?php echo e(__('Sent on')); ?>

                                        <?php echo e(\Auth::user()->dateFormat($bill->send_date)); ?>

                                    <?php else: ?>
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('send bill')): ?>
                                            <small><?php echo e(__('Status')); ?> : <?php echo e(__('Not Sent')); ?></small>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </p>

                                <?php if($bill->status == 0): ?>
                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('send bill')): ?>
                                        <a href="<?php echo e(route('bill.sent', $bill->id)); ?>" class="btn btn-sm btn-warning"
                                            data-bs-toggle="tooltip" data-original-title="<?php echo e(__('Mark Sent')); ?>"><i
                                                class="ti ti-send mr-2"></i><?php echo e(__('Send')); ?></a>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6 col-lg-4 col-xl-4">
                                <div class="timeline-icons"><span class="timeline-dots"></span>
                                    <i class="ti ti-report-money text-info"></i>
                                </div>
                                <h6 class="text-info my-3"><?php echo e(__('Get Paid')); ?></h6>
                                <p class="text-muted text-sm mb-3"><?php echo e(__('Status')); ?> : <?php echo e(__('Awaiting payment')); ?> </p>
                                <?php if($bill->status != 0): ?>
                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create payment bill')): ?>
                                        <a href="#" data-url="<?php echo e(route('bill.payment', $bill->id)); ?>" data-ajax-popup="true"
                                            data-title="<?php echo e(__('Add Payment')); ?>" class="btn btn-sm btn-info"
                                            data-original-title="<?php echo e(__('Add Payment')); ?>"><i
                                                class="ti ti-report-money mr-2"></i><?php echo e(__('Add Payment 22')); ?></a> <br>
                                    <?php endif; ?>
                                <?php endif; ?>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <?php if(\Auth::user()->type == 'company'): ?>
        <?php if($bill->status != 0): ?>
            <div class="row justify-content-between align-items-center mb-3">
                <div class="col-md-12 d-flex align-items-center justify-content-between justify-content-md-end">
                    <?php if(!empty($billPayment)): ?>
                        <div class="all-button-box mx-2">
                            <a href="#" data-url="<?php echo e(route('bill.debit.note', $bill->id)); ?>" data-ajax-popup="true"
                                data-title="<?php echo e(__('Add Debit Note')); ?>" class="btn btn-sm btn-primary">
                                <?php echo e(__('Add Debit Note')); ?>

                            </a>
                        </div>
                    <?php endif; ?>
                    <div class="all-button-box mx-2">
                        <a href="<?php echo e(route('bill.resent', $bill->id)); ?>" class="btn btn-sm btn-primary">
                            <?php echo e(__('Resend Bill')); ?>

                        </a>
                    </div>
                    <div class="all-button-box">
                        <a href="<?php echo e(route('bill.pdf', Crypt::encrypt($bill->id))); ?>" target="_blank"
                            class="btn btn-sm btn-primary">
                            <?php echo e(__('Download')); ?>

                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="row justify-content-between align-items-center mb-3">
            <div class="col-md-12 d-flex align-items-center justify-content-between justify-content-md-end">
                <div class="all-button-box mx-2">
                    <a href="#" data-url="<?php echo e(route('vender.bill.send', $bill->id)); ?>" data-ajax-popup="true"
                        data-title="<?php echo e(__('Send Bill')); ?>" class="btn btn-sm btn-primary">
                        <?php echo e(__('Send Mail')); ?>

                    </a>
                </div>
                <div class="all-button-box mx-2">
                    <a href="<?php echo e(route('bill.pdf', Crypt::encrypt($bill->id))); ?>" target="_blank"
                        class="btn btn-sm btn-primary">
                        <?php echo e(__('Download')); ?>

                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="invoice">
                        <div class="invoice-print">
                            <div class="row invoice-title mt-2">
                                <div class="col-xs-12 col-sm-12 col-nd-6 col-lg-6 col-12">
                                    <h2><?php echo e(__('Bill')); ?></h2>
                                </div>
                                <div class="col-xs-12 col-sm-12 col-nd-6 col-lg-6 col-12 text-end">
                                    <h3 class="invoice-number"><?php echo e(Auth::user()->billNumberFormat($bill->bill_id)); ?></h3>
                                </div>
                                <div class="col-12">
                                    <hr>
                                </div>
                            </div>



                            <div class="row">
                                <div class="col text-end">
                                    <div class="d-flex align-items-center justify-content-end">
                                        <div class="me-4">
                                            <small>
                                                <strong><?php echo e(__('Issue Date')); ?> :</strong><br>
                                                <?php echo e(\Auth::user()->dateFormat($bill->bill_date)); ?><br><br>
                                            </small>
                                        </div>
                                        <div>
                                            <small>
                                                <strong><?php echo e(__('Due Date')); ?> :</strong><br>
                                                <?php echo e(\Auth::user()->dateFormat($bill->due_date)); ?><br><br>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>


                            <div class="row">
                                <?php if(!empty($vendor->billing_name)): ?>
                                    <div class="col">
                                        <small class="font-style">
                                            <strong><?php echo e(__('Billed To')); ?> :</strong><br>
                                            <?php echo e(!empty($vendor->billing_name) ? $vendor->billing_name : ''); ?><br>
                                            <?php echo e(!empty($vendor->billing_address) ? $vendor->billing_address : ''); ?><br>
                                            <?php echo e(!empty($vendor->billing_city) ? $vendor->billing_city : '' . ', '); ?>,
                                            <?php echo e(!empty($vendor->billing_state) ? $vendor->billing_state : '', ', '); ?>

                                            <?php echo e(!empty($vendor->billing_zip) ? $vendor->billing_zip : ''); ?><br>
                                            <?php echo e(!empty($vendor->billing_country) ? $vendor->billing_country : ''); ?><br>
                                            <?php echo e(!empty($vendor->billing_phone) ? $vendor->billing_phone : ''); ?><br>
                                            <?php if(App\Models\Utility::getValByName('tax_number') == 'on'): ?>
                                                <strong><?php echo e(__('Tax Number ')); ?> :
                                                </strong><?php echo e(!empty($vendor->tax_number) ? $vendor->tax_number : ''); ?><br>
                                            <?php endif; ?>
                                            <?php if(!empty($settings['tax_type']) && !empty($settings['vat_number'])): ?><?php echo e($settings['tax_type'].' '. __('Number')); ?> : <?php echo e($settings['vat_number']); ?> <br><?php endif; ?>
                                        </small>
                                    </div>
                                <?php endif; ?>
                                <?php if(App\Models\Utility::getValByName('shipping_display') == 'on'): ?>
                                    <div class="col">
                                        <small>
                                            <strong><?php echo e(__('Shipped To')); ?> :</strong><br>
                                            <?php echo e(!empty($vendor->shipping_name) ? $vendor->shipping_name : ''); ?><br>
                                            <?php echo e(!empty($vendor->shipping_address) ? $vendor->shipping_address : ''); ?><br>
                                            <?php echo e(!empty($vendor->shipping_city) ? $vendor->shipping_city : '' . ', '); ?>,
                                            <?php echo e(!empty($vendor->shipping_state) ? $vendor->shipping_state : '', ', '); ?>

                                            <?php echo e(!empty($vendor->shipping_zip) ? $vendor->shipping_zip : ''); ?><br>
                                            <?php echo e(!empty($vendor->shipping_country) ? $vendor->shipping_country : ''); ?><br>
                                            <?php echo e(!empty($vendor->shipping_phone) ? $vendor->shipping_phone : ''); ?><br>
                                            <?php if(App\Models\Utility::getValByName('tax_number') == 'on'): ?>
                                                <strong><?php echo e(__('Tax Number ')); ?> :
                                                </strong><?php echo e(!empty($vendor->tax_number) ? $vendor->tax_number : ''); ?>

                                            <?php endif; ?>

                                        </small>
                                    </div>
                                <?php endif; ?>

                                <div class="col">
                                    <div class="float-end mt-3">
                                        <?php if($settings['bill_qr_display'] == 'on'): ?>
                                        <?php echo DNS2D::getBarcodeHTML(
                                            route('pay.billpay', \Illuminate\Support\Facades\Crypt::encrypt($bill->id)),
                                            'QRCODE',
                                            2,
                                            2,
                                        ); ?>

                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col">
                                    <small>
                                        <strong><?php echo e(__('Status')); ?> :</strong><br>
                                        <?php if($bill->status == 0): ?>
                                            <span
                                                class="badge fix_badge bg-primary p-2 px-3"><?php echo e(__(\App\Models\Status::getNameById($bill->status))); ?></span>
                                        <?php elseif($bill->status == 1): ?>
                                            <span
                                                class="badge fix_badge bg-info p-2 px-3"><?php echo e(__(\App\Models\Status::getNameById($bill->status))); ?></span>
                                        <?php elseif($bill->status == 2): ?>
                                            <span
                                                class="badge fix_badge bg-danger p-2 px-3"><?php echo e(__(\App\Models\Status::getNameById($bill->status))); ?></span>
                                        <?php elseif($bill->status == 3): ?>
                                            <span
                                                class="badge fix_badge bg-warning p-2 px-3"><?php echo e(__(\App\Models\Status::getNameById($bill->status))); ?></span>
                                        <?php elseif($bill->status == 4): ?>
                                            <span
                                                class="badge fix_badge bg-danger p-2 px-3"><?php echo e(__(\App\Models\Status::getNameById($bill->status))); ?></span>
                                        <?php endif; ?>
                                    </small>
                                </div>



                                <?php if(!empty($customFields) && count($bill->customField) > 0): ?>
                                    <?php $__currentLoopData = $customFields; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div class="col text-md-end">
                                            <small>
                                                <strong><?php echo e($field->name); ?> :</strong><br>
                                                <?php echo e(!empty($bill->customField) ? $bill->customField[$field->id] : '-'); ?>

                                                <br><br>
                                            </small>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <?php endif; ?>
                            </div>

                            <div class="row mt-4">
                                <div class="col-md-12">
                                    <div class="font-bold mb-2"><?php echo e(__('Product Summary')); ?></div>
                                    <small class="mb-2"><?php echo e(__('All items here cannot be deleted.')); ?></small>
                                    <div class="table-responsive mt-3">
                                        <table class="table mb-0 table-striped">
                                            <tr>
                                                <th class="text-dark" data-width="40">#</th>
                                                <th class="text-dark"><?php echo e(__('Product')); ?></th>
                                                <th class="text-dark"><?php echo e(__('Quantity')); ?></th>
                                                <th class="text-dark"><?php echo e(__('Rate')); ?></th>
                                                <th class="text-dark"><?php echo e(__('Discount')); ?></th>
                                                <th class="text-dark"><?php echo e(__('Tax')); ?></th>
                                                <th class="text-dark"><?php echo e(__('Chart Of Account')); ?></th>
                                                <th class="text-dark"><?php echo e(__('Account Amount')); ?></th>
                                                <th class="text-dark"><?php echo e(__('Description')); ?></th>
                                                <th class="text-end text-dark" width="12%"><?php echo e(__('Price')); ?><br>
                                                    <small class="text-danger font-weight-bold"><?php echo e(__('after tax & discount')); ?></small>
                                                </th>
                                                <th></th>
                                            </tr>
                                            <?php
                                                $totalQuantity=0;
                                               $totalRate=0;
                                               $totalTaxPrice=0;
                                               $totalDiscount=0;
                                               $taxesData=[];
                                            ?>


                                            <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key =>$item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                                                <?php if(!empty($item->product_id)): ?>
                                                        <tr>
                                                            <td><?php echo e($key+1); ?></td>

                                                            <?php
                                                                $productName = $item->product;
                                                                $totalQuantity += $item->quantity;
                                                        $totalRate += $item->price;
                                                        $totalDiscount += $item->discount;
                                                            ?>
                                                            <td><?php echo e(!empty($productName)?$productName->name:'-'); ?></td>
                                                            <td><?php echo e($item->quantity . ' (' . $productName->unit->name . ')'); ?></td>
                                                            <td><?php echo e(\Auth::user()->priceFormat($item->price)); ?></td>
                                                            <td><?php echo e(\Auth::user()->priceFormat($item->discount)); ?></td>

                                                            <td>
                                                                <?php if(!empty($item->tax)): ?>
                                                                    <table>
                                                                        <?php
                                                                            $itemTaxes = [];
                                                                            $getTaxData = Utility::getTaxData();

                                                                            if (!empty($item->tax)) {
                                                                                foreach (explode(',', $item->tax) as $tax) {
                                                                                    $taxPrice = Utility::taxRate($getTaxData[$tax]['rate'], $item->price, $item->quantity);
                                                                                    $totalTaxPrice += $taxPrice;
                                                                                    $itemTax['name'] = $getTaxData[$tax]['name'];
                                                                                    $itemTax['rate'] = $getTaxData[$tax]['rate'] . '%';
                                                                                    $itemTax['price'] = \Auth::user()->priceFormat($taxPrice);

                                                                                    $itemTaxes[] = $itemTax;
                                                                                    if (array_key_exists($getTaxData[$tax]['name'], $taxesData)) {
                                                                                        $taxesData[$getTaxData[$tax]['name']] = $taxesData[$getTaxData[$tax]['name']] + $taxPrice;
                                                                                    } else {
                                                                                        $taxesData[$getTaxData[$tax]['name']] = $taxPrice;
                                                                                    }
                                                                                }
                                                                                $item->itemTax = $itemTaxes;
                                                                            } else {
                                                                                $item->itemTax = [];
                                                                            }
                                                                        ?>
                                                                        <?php $__currentLoopData = $item->itemTax; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tax): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                                                                                <tr>
                                                                                    <td><?php echo e($tax['name'] .' ('.$tax['rate'] .'%)'); ?></td>
                                                                                    <td><?php echo e($tax['price']); ?></td>
                                                                                </tr>
                                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                                    </table>
                                                                <?php else: ?>
                                                                    -
                                                                <?php endif; ?>
                                                            </td>

                                                            <?php
                                                                $chartAccount = \App\Models\ChartOfAccount::find($item->chart_account_id);
                                                            ?>

                                                            <td><?php echo e(!empty($chartAccount) ? $chartAccount->name : '-'); ?></td>
                                                            <td><?php echo e(\Auth::user()->priceFormat($item->amount)); ?></td>

                                                            <td><?php echo e(!empty($item->description)?$item->description:'-'); ?></td>

                                                            <td class="text-end"><?php echo e(\Auth::user()->priceFormat(($item->price * $item->quantity - $item->discount) + $totalTaxPrice)); ?></td>
                                                            <td></td>
                                                        </tr>
                                                    <?php else: ?>
                                                    <tr>
                                                        <td><?php echo e($key+1); ?></td>
                                                        <td>-</td>
                                                        <td>-</td>
                                                        <td>-</td>
                                                        <td>-</td>
                                                        <td>-</td>
                                                        <?php
                                                            $chartAccount = \App\Models\ChartOfAccount::find($item['chart_account_id']);
                                                        ?>
                                                        <td><?php echo e(!empty($chartAccount) ? $chartAccount->name : '-'); ?></td>
                                                        <td><?php echo e(\Auth::user()->priceFormat($item['amount'])); ?></td>
                                                        <td>-</td>
                                                        <td class="text-end"><?php echo e(\Auth::user()->priceFormat($item['amount'])); ?></td>
                                                        <td></td>


                                                    </tr>

                                                <?php endif; ?>


                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            <tfoot>
                                            <tr>
                                                <td></td>
                                                <td><b><?php echo e(__('Total')); ?></b></td>
                                                <td><b><?php echo e($totalQuantity); ?></b></td>
                                                <td><b><?php echo e(\Auth::user()->priceFormat($totalRate)); ?></b></td>
                                                <td><b><?php echo e(\Auth::user()->priceFormat($totalDiscount)); ?></b></td>
                                                <td><b><?php echo e(\Auth::user()->priceFormat($totalTaxPrice)); ?></b></td>
                                                <td></td>
                                                <td><b><?php echo e(\Auth::user()->priceFormat($bill->getAccountTotal())); ?></b></td>

                                            </tr>
                                            <tr>
                                                <td colspan="8"></td>
                                                <td class="text-end"><b><?php echo e(__('Sub Total')); ?></b></td>
                                                <td class="text-end"><?php echo e(\Auth::user()->priceFormat($bill->getSubTotal())); ?></td>
                                            </tr>

                                                <tr>
                                                    <td colspan="8"></td>
                                                    <td class="text-end"><b><?php echo e(__('Discount')); ?></b></td>
                                                    <td class="text-end"><?php echo e(\Auth::user()->priceFormat($bill->getTotalDiscount())); ?></td>
                                                </tr>

                                            <?php if(!empty($taxesData)): ?>
                                                <?php $__currentLoopData = $taxesData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $taxName => $taxPrice): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <tr>
                                                        <td colspan="8"></td>
                                                        <td class="text-end"><b><?php echo e($taxName); ?></b></td>
                                                        <td class="text-end"><?php echo e(\Auth::user()->priceFormat($taxPrice)); ?></td>
                                                    </tr>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            <?php endif; ?>
                                            <tr>
                                                <td colspan="8"></td>
                                                <td class="blue-text text-end"><b><?php echo e(__('Total')); ?></b></td>
                                                <td class="blue-text text-end"><?php echo e(\Auth::user()->priceFormat($bill->getTotal())); ?></td>
                                            </tr>
                                            <tr>
                                                <td colspan="8"></td>
                                                <td class="text-end"><b><?php echo e(__('Paid')); ?></b></td>
                                                <td class="text-end"><?php echo e(\Auth::user()->priceFormat(($bill->getTotal()-$bill->getDue())-($bill->billTotalDebitNote()))); ?></td>
                                            </tr>
                                            <tr>
                                                <td colspan="8"></td>
                                                <td class="text-end"><b><?php echo e(__('Debit Note')); ?></b></td>
                                                <td class="text-end"><?php echo e(\Auth::user()->priceFormat(($bill->billTotalDebitNote()))); ?></td>
                                            </tr>
                                            <tr>
                                                <td colspan="8"></td>
                                                <td class="text-end"><b><?php echo e(__('Due')); ?></b></td>
                                                <td class="text-end"><?php echo e(\Auth::user()->priceFormat($bill->getDue())); ?></td>
                                            </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <h5 class="h4 d-inline-block mb-3"><?php echo e(__('Payment Summary')); ?></h5>
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table">
                            <tr>
                                <th class="text-dark"><?php echo e(__('Payment Receipt')); ?></th>
                                <th class="text-dark"><?php echo e(__('Date')); ?></th>
                                <th class="text-dark"><?php echo e(__('Amount')); ?></th>
                                <th class="text-dark"><?php echo e(__('Account')); ?></th>
                                <th class="text-dark"><?php echo e(__('Reference')); ?></th>
                                <th class="text-dark"><?php echo e(__('Description')); ?></th>
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete payment bill')): ?>
                                    <th class="text-dark"><?php echo e(__('Action')); ?></th>
                                <?php endif; ?>
                            </tr>
                            <?php $__empty_1 = true; $__currentLoopData = $bill->payments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key =>$payment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <?php
                                    $paymentpath = \App\Models\Utility::get_file('uploads/payment');
                                ?>
                                <tr>
                                    <td>
                                        <?php if(!empty($payment->add_receipt)): ?>
                                            <a href="<?php echo e($paymentpath . '/' . $payment->add_receipt); ?>" download=""
                                                class="btn btn-sm btn-primary btn-icon" target="_blank"
                                                data-bs-toggle="tooltip" title="<?php echo e(__('Download')); ?>"><span
                                                    class="btn-inner--icon"><i class="ti ti-download"></i></span></a>
                                            <a href="<?php echo e($paymentpath . '/' . $payment->add_receipt); ?>"
                                                class="btn btn-sm btn-secondary btn-icon"
                                                target="_blank" data-bs-toggle="tooltip" title="<?php echo e(__('Preview')); ?>"><span class="btn-inner--icon"><i
                                                        class="ti ti-crosshair"></i></span></a>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo e(\Auth::user()->dateFormat($payment->date)); ?></td>
                                    <td><?php echo e(\Auth::user()->priceFormat($payment->amount)); ?></td>
                                    <td><?php echo e(!empty($payment->bankAccount) ? $payment->bankAccount->bank_name . ' ' . $payment->bankAccount->holder_name : ''); ?>

                                    </td>
                                    <td><?php echo e($payment->reference); ?></td>
                                    <td><?php echo e($payment->description); ?></td>
                                    <td class="text-dark">
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete bill product')): ?>
                                            <div class="action-btn me-2">
                                                <?php echo Form::open([
                                                    'method' => 'post',
                                                    'route' => ['bill.payment.destroy', $bill->id, $payment->id],
                                                    'id' => 'delete-form-' . $payment->id,
                                                ]); ?>

                                                <a href="#" class="mx-3 btn btn-sm  align-items-center bs-pass-para bg-danger"
                                                    data-bs-toggle="tooltip" title="<?php echo e(__('Delete')); ?>"
                                                    data-original-title="<?php echo e(__('Delete')); ?>"
                                                    data-confirm="<?php echo e(__('Are You Sure?') . '|' . __('This action can not be undone. Do you want to continue?')); ?>"
                                                    data-confirm-yes="document.getElementById('delete-form-<?php echo e($payment->id); ?>').submit();">
                                                    <i class="ti ti-trash text-white text-white"></i>
                                                </a>
                                                <?php echo Form::close(); ?>

                                            </div>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="6" class="text-center text-dark">
                                        <p><?php echo e(__('No Data Found')); ?></p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <h5 class="h4 d-inline-block mb-3"><?php echo e(__('Debit Note Summary')); ?></h5>
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table">
                            <tr>
                                <th class="text-dark"><?php echo e(__('Date')); ?></th>
                                <th class="text-dark"><?php echo e(__('Amount')); ?></th>
                                <th class="text-dark"><?php echo e(__('Description')); ?></th>
                                <?php if(Gate::check('edit debit note') || Gate::check('delete debit note')): ?>
                                    <th class="text-dark"><?php echo e(__('Action')); ?></th>
                                <?php endif; ?>
                            </tr>
                            <?php $__empty_1 = true; $__currentLoopData = $bill->debitNote; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key =>$debitNote): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td><?php echo e(\Auth::user()->dateFormat($debitNote->date)); ?></td>
                                    <td><?php echo e(\Auth::user()->priceFormat($debitNote->amount)); ?></td>
                                    <td><?php echo e($debitNote->description); ?></td>
                                    <td>
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('edit debit note')): ?>
                                            <div class="action-btn me-2">
                                                <a data-url="<?php echo e(route('bill.edit.debit.note', [$debitNote->bill, $debitNote->id])); ?>"
                                                    data-ajax-popup="true" data-title="<?php echo e(__('Add Debit Note')); ?>"
                                                    href="#" class="mx-3 btn btn-sm align-items-center bg-info"
                                                    data-bs-toggle="tooltip" data-original-title="<?php echo e(__('Edit')); ?>">
                                                    <i class="ti ti-pencil text-white"></i>
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete debit note')): ?>
                                            <div class="action-btn">
                                                <?php echo Form::open([
                                                    'method' => 'DELETE',
                                                    'route' => ['bill.delete.debit.note', $debitNote->bill, $debitNote->id],
                                                    'id' => 'delete-form-' . $debitNote->id,
                                                ]); ?>

                                                <a href="#" class="mx-3 btn btn-sm  align-items-center bs-pass-para bg-danger"
                                                    data-bs-toggle="tooltip" title="<?php echo e(__('Delete')); ?>"
                                                    data-original-title="<?php echo e(__('Delete')); ?>"
                                                    data-confirm="<?php echo e(__('Are You Sure?') . '|' . __('This action can not be undone. Do you want to continue?')); ?>"
                                                    data-confirm-yes="document.getElementById('delete-form-<?php echo e($debitNote->id); ?>').submit();">
                                                    <i class="ti ti-trash text-white text-white"></i>
                                                </a>
                                                <?php echo Form::close(); ?>

                                            </div>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="4" class="text-center text-dark">
                                        <p><?php echo e(__('No Data Found')); ?></p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\willt\Desktop\Workspace Bladimir\codecanyon-aIPVP9oe-accountgo-accounting-and-billing-tool\main-file\resources\views/bill/view.blade.php ENDPATH**/ ?>