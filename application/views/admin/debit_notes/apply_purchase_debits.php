<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php if ((debits_can_be_applied_to_purchase($purchase->status) && $debits_available > 0)) { ?>
    <div class="modal fade apply-debit-from-purchase" id="apply_debits" data-balance-due="<?php echo $purchase->total_left_to_pay; ?>" tabindex="-1" role="dialog" aria-labelledby="modalLabelApplyDebits">
        <div class="modal-dialog modal-lg" role="document">
            <?php echo form_open(admin_url('purchase/apply_debits/' . $purchase->id), array('id' => 'apply_debits_form')); ?>
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="modalLabelApplyDebits">
                        <?php echo format_purchase_number($purchase->id); ?> - <?php echo _l('apply_debits'); ?>
                    </h4>
                </div>
                <div class="modal-body">
                    <div class="table-responsive debits-table">
                        <table class="table table-bordered no-mtop">
                            <thead>
                                <tr>
                                    <th><span class="bold">Debit Note #</span></th>
                                    <th><span class="bold">Debit Note Date</span></th>
                                    <th><span class="bold">Debit Amount</span></th>
                                    <th><span class="bold">Debit Available</span></th>
                                    <th><span class="bold">Amount to Debit</span></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($open_debits as $debit) { ?>
                                    <tr>
                                        <td><a href="<?php echo admin_url('debit_notes/list_debit_notes/' . $debit['id']); ?>" target="_blank"><?php echo format_debit_note_number($debit['id']); ?></a></td>
                                        <td><?php echo _d($debit['date']); ?></td>
                                        <td><?php echo app_format_money($debit['total'], $purchase_currency) ?></td>
                                        <td><?php echo app_format_money($debit['available_debits'], $purchase_currency) ?></td>
                                        <td>
                                            <input type="number" max="<?php echo $debit['available_debits']; ?>" name="amount[<?php echo $debit['id']; ?>]" class="form-control apply-debit-field" value="0">
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="row">
                        <div class="col-md-6 col-md-offset-6">
                            <div class="text-right">
                                <table class="table">
                                    <tbody>
                                        <tr>
                                            <td class="bold">Amount to Debit:</td>
                                            <td class="amount-to-debit">
                                                <?php echo app_format_money(0, $purchase->currency_name); ?>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                    <button type="submit" class="btn btn-info"><?php echo _l('apply'); ?></button>
                </div>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
    <script>
        $('body').addClass('no-calculate-total');
        init_currency(<?php echo $purchase->currency; ?>);
        $(function() {
            appValidateForm('#apply_debits_form');

            $('body').on('change blur', '.apply-debit-from-purchase .apply-debit-field', function() {

                var $applyCredits = $('#apply_debits');
                var $amountInputs = $applyCredits.find('input.apply-debit-field');
                var total = 0;

                $.each($amountInputs, function() {
                    if ($(this).valid() === true) {
                        var amount = $(this).val();
                        amount = parseFloat(amount);
                        if (!isNaN(amount)) {
                            total += amount;
                        } else {
                            $(this).val(0);
                        }
                    }
                });
                $applyCredits.find('.amount-to-debit').html(format_money(total));
            });
        });
    </script>
<?php } ?>