<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php if ($debit_note->status == 1) { ?>
    <!-- Modal Apply Credits -->
    <div class="modal fade apply-debits-to-purchase" id="apply_debits" data-credits-remaining="<?php echo $debit_note->remaining_debits; ?>" tabindex="-1" role="dialog" aria-labelledby="modalLabelApplyCredits">
        <div class="modal-dialog modal-lg" role="document">
            <?php echo form_open(admin_url('debit_notes/apply_debits_to_purchase/' . $debit_note->id), array('id' => 'apply_debits_form')); ?>
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="modalLabelApplyCredits">Apply Debits From <?= format_debit_note_number($debit_note->id) ?></h4>
                </div>
                <div class="modal-body">
                    <?php if (count($available_debitable_purchase) > 0) { ?>
                        <div class="table-responsive credits-table">
                            <table class="table table-bordered no-mtop">
                                <thead>
                                    <tr>
                                        <th><span class="bold">Purchase Number #</span></th>
                                        <th><span class="bold">Purchase Date</span></th>
                                        <th><span class="bold">Purchase Amount</span></th>
                                        <th><span class="bold">Amount To Debit</span></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($available_debitable_purchase as $purchase) {
                                    ?>
                                        <tr>
                                            <td><a href="<?php echo admin_url('purchase/list_purchase/' . $purchase['id']); ?>" target="_blank"><?php echo format_purchase_number($purchase['id']); ?></a></td>
                                            <td><?php echo _d($purchase['date']); ?></td>
                                            <td><?php echo app_format_money($purchase['total'], $purchase['currency_name']) ?></td>
                                            <td>
                                                <input type="number" name="amount[<?php echo $purchase['id']; ?>]" class="form-control apply-debit-field" value="0">
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
                                                <td class="bold">Available Debit Note Amount:</td>
                                                <td class="debit-not-avl-amount">
                                                    <?php echo app_format_money($debit_note->remaining_debits, $debit_note->currency_name); ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="bold">Amount to Debit:</td>
                                                <td class="amount-to-debit">
                                                    <?php echo app_format_money(0, $debit_note->currency_name); ?>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    <?php } else { ?>
                        <p class="bold no-mbot">Purchase not available.</p>
                    <?php } ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                    <?php if (count($available_debitable_purchase) > 0) { ?>
                        <button type="submit" class="btn btn-info"><?php echo _l('apply'); ?></button>
                    <?php } ?>
                </div>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
    <script>
        $('body').addClass('no-calculate-total');
        init_currency(<?php echo $debit_note->currencyid; ?>);
        $(function() {
            appValidateForm('#apply_debits_form');

            $(document).on('input', '.apply-debits-to-purchase .apply-debit-field', function() {
                var $applyCredits = $('#apply_debits');
                var $amountInputs = $applyCredits.find('input.apply-debit-field');
                var total = 0;
                var avl_amount_raw = $('.debit-not-avl-amount').text();
                var avl_amount_cleaned = avl_amount_raw.replace(/[^0-9.\-]+/g, '');
                var avl_amount = parseFloat(avl_amount_cleaned) || 0;
                $.each($amountInputs, function() {
                    if ($(this).valid() === true) {
                        var amount = $(this).val();
                        amount = parseFloat(amount);
                        if (!isNaN(amount)) {
                            total += amount;
                        }
                    }
                });
                $('#apply_debits').find('button[type="submit"]').prop('disabled', false);
                $('.amount-to-debit').closest('tbody').find('.text-danger').remove();
                if (total > avl_amount) {
                    $('#apply_debits').find('button[type="submit"]').prop('disabled', true);
                    $('.amount-to-debit').closest('tbody').append('<tr><td class="text-danger" colspan="2">Debit amount exceeds available amount.</td></tr>');
                }
                $applyCredits.find('.amount-to-debit').html(format_money(total));
            });
        });
    </script>
<?php } ?>