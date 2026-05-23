<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style>
    .amount-row {
        margin-top: 10px;
        margin-bottom: 20px;
        font-size: 16px;
    }
</style>
<?php echo form_open_multipart(admin_url('contracts/save_payment_terms'), array('id' => 'payment-terms-form')); ?>
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title" id="myModalLabel">
        <?php echo $title; ?>
    </h4>
</div>
<div class="modal-body">
    <?php if (!isset($payment['id'])) { ?>
        <div class="row amount-row">
            <div class="col-md-6">Agreement Amount : <b><span id="total_contract_amount"><?= $contract_value ?></span></b></div>
            <div class="col-md-6">Pending Amount : <b><span id="total_pending_amount"><?= $pending_amount ?></span></b></div>
        </div>
        <hr />
    <?php } ?>
    <input type="hidden" name="id" form="payment-terms-form" value="<?= (isset($payment['id']) ? $payment['id'] : "") ?>" />
    <input type="hidden" name="contract_id" form="payment-terms-form" value="<?= (isset($contract_id) ? $contract_id : "") ?>" />

    <?php if (isset($payment['id'])) { ?>
        <?php if ($payment['status'] == "Pending") { ?>
            <div class="row editrow">
                <?= all_type_input_render([
                    "label" => "Percentage Of Total Agreement Value",
                    "id" => "percentage",
                    "name" => "percentage",
                    "className" => "pecentage_input",
                    "type" => "text",
                    "is_required" => true,
                    "is_readonly" => true,
                    "form" => "payment-terms-form",
                    "selected_value" => (isset($payment['percentage']) ? $payment['percentage'] : $pending_percentage),
                ], 'col-md-6', true);
                ?>
                <?= all_type_input_render([
                    "label" => "Payment Amount",
                    "id" => "amount",
                    "className" => "amount_input",
                    "name" => "amount",
                    "type" => "text",
                    "is_required" => true,
                    "form" => "payment-terms-form",
                    "is_readonly" => true,
                    "selected_value" => (isset($payment['amount']) ? $payment['amount'] : $pending_amount),
                ], 'col-md-6', true);
                ?>
                <div class="clearfix"></div>
                <?= all_type_input_render([
                    "label" =>  "Payment Due Date",
                    "id" => "scheduled_payment_date",
                    "name" => "scheduled_payment_date",
                    "type" => "date_picker",
                    "is_required" => true,
                    "form" => "payment-terms-form",
                    "selected_value" => (isset($payment['scheduled_payment_date']) ? _d($payment['scheduled_payment_date']) : ""),
                ], 'col-md-6', true);
                ?>
                <?= all_type_input_render([
                    "label" =>  "Note",
                    "id" => "note",
                    "name" => "note",
                    "type" => "textarea",
                    "rows" => 4,
                    "is_required" => true,
                    "form" => "payment-terms-form",
                    "selected_value" => (isset($payment['note']) ? $payment['note'] : ""),
                ], 'col-md-6', true);
                ?>
            </div>
        <?php } else { ?>
            <div class="row">
                <div class="col-md-12">
                    <table class="table table-bordered">
                        <tr>
                            <td><strong>Percentage Of Total Agreement Value</strong></td>
                            <td><?= $payment['percentage'] . "%" ?></td>
                        </tr>
                        <tr>
                            <td><strong>Payment Amount</strong></td>
                            <td><?= $payment['amount'] ?></td>
                        </tr>
                        <tr>
                            <td><strong>Payment Status</strong></td>
                            <td><?= $payment['note'] ?></td>
                        </tr>
                        <tr>
                            <td><strong>Payment Due Date</strong></td>
                            <td><?= _d($payment['scheduled_payment_date']) ?></td>
                        </tr>
                        <tr>
                            <td><strong>Note </strong></td>
                            <td><?= $payment['note'] ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        <?php } ?>
    <?php } else { ?>
        <div class="row new-row">
            <div class="col-md-12">
                <div class="panel payment-panel panel-default">
                    <div class="panel-heading">
                        <h4 class="panel-title">Payment Details</h4>
                    </div>
                    <div class="panel-body">
                        <div class="row">
                            <?= all_type_input_render([
                                "label" => "Percentage Of Total Agreement Value",
                                "id" => "percentage_0",
                                "name" => "percentage[]",
                                "type" => "text",
                                "is_required" => true,
                                "form" => "payment-terms-form",
                                "className" => "pecentage_input ",
                                "selected_value" => $pending_percentage,
                            ], 'col-md-6', true);
                            ?>
                            <?= all_type_input_render([
                                "label" => "Payment Amount",
                                "id" => "amount_0",
                                "name" => "amount[]",
                                "type" => "text",
                                "is_required" => true,
                                "form" => "payment-terms-form",
                                "className" => "amount_input ",
                                "selected_value" => $pending_amount,
                            ], 'col-md-6', true);
                            ?>
                            <div class="clearfix"></div>
                            <?= all_type_input_render([
                                "label" =>  "Payment Due Date",
                                "id" => "scheduled_payment_date_0",
                                "name" => "scheduled_payment_date[]",
                                "type" => "date_picker",
                                "is_required" => true,
                                "className" => "scheduled_payment_date_input ",
                                "form" => "payment-terms-form",
                            ], 'col-md-6', true);
                            ?>
                            <?= all_type_input_render([
                                "label" =>  "Note",
                                "id" => "note_0",
                                "name" => "note[]",
                                "type" => "textarea",
                                "rows" => 4,
                                "is_required" => true,
                                "className" => "note_input ",
                                "form" => "payment-terms-form",
                            ], 'col-md-6', true);
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php } ?>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
    <?php if (isset($payment['status'])) { ?>
        <?php if ($payment['status'] == "Pending") { ?>
            <button type="submit" class="btn btn-info btn-submit"><?php echo _l('submit'); ?></button>
        <?php } ?>
    <?php } else { ?>
        <button type="submit" class="btn btn-info btn-submit"><?php echo _l('submit'); ?></button>
    <?php } ?>
</div>
<?php echo form_close(); ?>