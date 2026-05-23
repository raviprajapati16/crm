<?php defined('BASEPATH') or exit('No direct script access allowed');
?>
<style>
    .table-pdf {
        width: 100%;
        border-collapse: collapse;
    }

    .table-pdf th,
    .table-pdf td {
        border: 1px solid black;
        padding: 8px;
    }
</style>
<div style="font-size: 16px; font-weight:bold; margin-bottom:10px;">Payment Received : </div>
<table class="table-pdf">
    <thead>
        <tr>
            <th width="20%">#</th>
            <th width="30%">Payment Mode</th>
            <th width="10%">Received Date</th>
            <th width="10%">Amount</th>
            <th width="30%">Note</th>
        </tr>
    </thead>
    <tbody>
        <?php
        foreach ($payments as $payment) {
            $invoice_id = "";
            if (!empty($payment['invoiceid']) && $payment['invoiceid'] != 0) {
                $invoice_id = $payment['invoiceid'];
            } else {
                $this->db->where_in('id', $proposal_id);
                $this->db->select('id,currency');
                $proposal = $this->db->from(db_prefix() . 'proposals')->get()->row();
                if (!empty($proposal)) {
                    $invoice_id = get_proposal_invoice_id($proposal->id);
                }
            }
            if (!empty($invoice_id) && $invoice_id != 0) {
                $invoice = $this->invoices_model->get($invoice_id);
            }
        ?>
            <tr>
                <td>
                    <b>Payment ID</b><br>#<?php echo $payment['paymentid']; ?> <br>
                    <?= (!empty(!empty($invoice_id) && $invoice_id != 0)) ? "<b>Invoice No.</b><br>#" . format_invoice_number($invoice_id) : ""  ?>
                </td>
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
                <td><?php echo app_format_money($payment['amount'], $currency).'/-'; ?></td>
                <td><?php echo $payment['note']; ?></td>
            </tr>
        <?php } ?>
    </tbody>
</table>