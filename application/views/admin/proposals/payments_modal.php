<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style>
    .amount-row {
        margin-top: 10px;
        margin-bottom: 20px;
        font-size: 16px;
    }
</style>
<?php echo form_open_multipart(admin_url('proposals/save_payment'), array('id' => 'payment-form')); ?>
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title" id="myModalLabel">
        <?php echo $title; ?>
    </h4>
</div>
<div class="modal-body">
    <?php if (isset($contract_id)) { ?>
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label for="payment_proposal_id">Proposal <span class="text-danger"> *</span></label>
                    <select id="payment_proposal_id" name="proposal_id" class="selectpicker" data-width="100%" data-none-selected-text="Select Proposal" data-actions-box="1" data-live-search="true" tabindex="-98" required>
                        <option value=""></option>
                        <?php if (isset($proposals) && !empty($proposals)) {
                            foreach ($proposals as $key => $proposal_data) {
                                if ($proposal_data['payment_data']['remaining_amount'] > 0 || (isset($payment) && $payment->proposal_id == $proposal_data['id'])) {
                        ?>
                                    <option
                                        value="<?= $proposal_data['id'] ?>"
                                        data-invoice-id="<?= $proposal_data['invoice_id'] ?>"
                                        data-total-amount="<?= $proposal_data['payment_data']['total_amount'] ?>"
                                        data-remaining-amount="<?= $proposal_data['payment_data']['remaining_amount'] + (isset($payment->amount) && $payment->proposal_id == $proposal_data['id'] ? $payment->amount : 0) ?>"
                                        data-total-received-amount="<?= $proposal_data['payment_data']['total_received_amount'] ?>"
                                        <?= (isset($payment->paymentid) && $payment->proposal_id == $proposal_data['id'] ? "selected" : "") ?>>
                                        <?= format_proposal_number($proposal_data['id']) ?>
                                    </option>
                            <?php
                                }
                            }
                            ?>
                        <?php } ?>
                    </select>
                </div>
            </div>
        </div>
        <input type="hidden" name="contract_id" value="<?= $contract_id ?>" />
        <div class="clearfix"></div>
        <div class="row amount-row">
            <div class="col-md-4">Total Amount : <b><span id="total_contract_amount">0</span></b></div>
            <div class="col-md-4">Total Received Amount : <b><span id="total_received_amount">0</span></b></div>
            <div class="col-md-4">Remaining Amount : <b><span id="total_pending_amount">0</span></b></div>
        </div>
    <?php } else { ?>
        <div class="row amount-row">
            <div class="col-md-6">Total Amount : <b><span id="total_contract_amount"><?= $payment_data['total_amount'] ?></span></b></div>
            <div class="col-md-6">Applied Credit : <b><span id="applied_credit"><?= $payment_data['applied_credits'] ?></span></b></div>
            <div class="col-md-6">Total Received Amount : <b><span id="total_received_amount"><?= $payment_data['total_received_amount'] ?></span></b></div>
            <div class="col-md-6">Remaining Amount : <b><span id="total_pending_amount"><?= $payment_data['remaining_amount'] + (isset($payment->amount) ? $payment->amount : 0) ?></span></b></div>
        </div>
        <input type="hidden" name="proposal_id" value="<?= ($proposal_id) ? $proposal_id : ''  ?>" />
    <?php }  ?>
    <hr />
    <input type="hidden" name="id" value="<?= (isset($payment->paymentid) ? $payment->paymentid : "") ?>" />
    <input type="hidden" name="invoiceid" value="<?= (isset($invoice_id)) ? $invoice_id : ""  ?>" />
    <div class="row">
        <div class="col-md-6">
            <?php echo render_input('amount', 'payment_edit_amount_received', (isset($payment->amount) ? $payment->amount : $payment_data['remaining_amount']), 'number', ["required" => true]) ?>
        </div>
        <div class="col-md-6">
            <?php echo render_date_input('date', 'payment_edit_date', (isset($payment->date) ? _d($payment->date) : date('d-m-Y')), ["required" => true]) ?>
        </div>
        <div class="clearfix"></div>
        <div class="col-md-6">
            <?php echo render_select('paymentmode', $payment_modes, array('id', 'name'), 'payment_mode', $payment->paymentmode); ?>
        </div>
        <div class="col-md-6">
            <?php echo render_input('paymentmethod', 'payment_method', (isset($payment->paymentmethod) ? $payment->paymentmethod : "")) ?>
        </div>
        <div class="clearfix"></div>
        <div class="col-md-6">
            <?php echo render_input('transactionid', 'payment_transaction_id', (isset($payment->transactionid) ? $payment->transactionid : "")) ?>
        </div>
        <div class="col-md-6">
            <?php echo render_textarea('note', 'note', (isset($payment->note) ? $payment->note : ""), array('rows' => 7)); ?>
        </div>
        <div class="clearfix"></div>
        <?php if (!isset($payment->paymentid)) { ?>
            <div class="col-md-6">
                <div class="checkbox checkbox-primary mtop15 inline-block">
                    <input type="checkbox" name="do_not_send_email_template" id="do_not_send_email_template">
                    <label for="do_not_send_email_template">Do not send invoice payment recorded email to customer contacts </label>
                </div>
            </div>
        <?php } ?>
    </div>
</div>
<div class="modal-footer">
    <button type="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
    <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
</div>
<?php echo form_close(); ?>