<?php defined('BASEPATH') or exit('No direct script access allowed');
?>
<style>
    .payment-table-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
</style>
<div class="payment-table-header">
    <h4 class="no-mbot">Payment Received</h4>
    <?php if (!empty($contract_id)) { ?>
        <button type="button" class="btn btn-primary btn-payment-create" <?= (get_contract_payment_data($contract_id)['remaining_amount'] == 0) ? 'disabled' : '' ?>>Create</button>
    <?php } else { ?>
        <button type="button" class="btn btn-primary btn-payment-create" <?= (get_proposal_payment_data($proposal->id)['remaining_amount'] == 0) ? 'disabled' : '' ?>>Create</button>
    <?php } ?>
</div>
<hr />
<?php
if (count($payments) === 0) {
    echo '<h4 class="text-center">Payments Not Available</h4>';
} else {
?>
    <div class="table-responsive">
        <table class="table table-bordered table-hover no-mtop">
            <thead>
                <tr>
                    <th width="10%"><span class="bold"><?php echo _l('payments_table_number_heading'); ?></span></th>
                    <th width="<?= (!empty($contract_id)) ? "8%" : "15%" ?>"><span class="bold">Invoice ID</th>
                    <?php if (!empty($contract_id)) { ?>
                        <th width="7%"><span class="bold">Proposal</span></th>
                    <?php } ?>
                    <th width="40%"><span class="bold"><?php echo _l('payments_table_mode_heading'); ?></span></th>
                    <th width="10%"><span class="bold"><?php echo _l('payments_table_date_heading'); ?></span></th>
                    <th width="10%"><span class="bold"><?php echo _l('payments_table_amount_heading'); ?></span></th>
                    <th width="15%"><span class="bold"><?php echo _l('options'); ?></span></th>
                </tr>
            </thead>
            <tbody>
                <?php
                $currency = get_base_currency()->id;
                foreach ($payments as $payment) {
                    $invoice_id = "";
                    if (!empty($payment['proposal_id'])) {
                        $proposal = $this->proposals_model->get($payment['proposal_id']);
                        if (!empty($proposal)) {
                            $currency = $proposal->currency;
                            $invoice_id = get_proposal_invoice_id($proposal->id);
                        }
                    } else {
                        $invoice_id = $payment['invoiceid'];
                    }
                    if (!empty($invoice_id) && $invoice_id != 0) {
                        $invoice = $this->invoices_model->get($invoice_id);
                        $currency = $invoice->currency_name;
                    }
                ?>
                    <tr class="payment">
                        <td> <a href="<?= admin_url('payments/payment/' . $payment['paymentid']) ?>" target='_blank'><?php echo $payment['paymentid']; ?></a>
                            <?php echo icon_btn('payments/pdf/' . $payment['paymentid'], 'file-pdf-o', 'btn-default pull-right', ["target" => "_blank"]); ?>
                        </td>
                        <td><?= ($invoice_id) ? "<a href=" . admin_url('invoices#' . $invoice_id) . " target='_blank'>" . format_invoice_number($invoice_id) . "</a>" : "N/A" ?></td>
                        <?php if (!empty($contract_id)) { ?>
                            <td> <a href="<?= admin_url('proposals#' . $payment['proposal_id']) ?>" target='_blank'><?= format_proposal_number($payment['proposal_id']); ?></a>
                            </td>
                        <?php } ?>
                        <td><?php echo $payment['name']; ?>
                            <?php if (!empty($payment['paymentmethod'])) {
                                echo ' - ' . $payment['paymentmethod'];
                            }
                            if ($payment['transactionid']) {
                                echo '<br />' . _l('payments_table_transaction_id', $payment['transactionid']);
                            }
                            ?>
                        </td>
                        <td><?php echo _d($payment['date']); ?></td>
                        <td>
                            <?php echo app_format_money($payment['amount'], $currency); ?></td>
                        <td>
                            <a href="javascript:;" data-id="<?= $payment['paymentid'] ?>" class="btn btn-default btn-icon btn-edit-payment"><i class="fa fa-pencil-square-o"></i></a>
                            <?php if (has_permission('payments', '', 'delete')) { ?>
                                <?php if (!empty($contract_id)) { ?>
                                    <a href="<?php echo admin_url('proposals/delete_payment/' . $payment['paymentid'] . '/' . $proposal->id . '/' . $contract_id); ?>" class="btn btn-danger btn-icon _delete"><i class="fa fa-trash"></i></a>
                                <?php } else {  ?>
                                    <a href="<?php echo admin_url('proposals/delete_payment/' . $payment['paymentid'] . '/' . $proposal->id); ?>" class="btn btn-danger btn-icon _delete"><i class="fa fa-trash"></i></a>
                                <?php }  ?>
                            <?php } ?>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
<?php } ?>