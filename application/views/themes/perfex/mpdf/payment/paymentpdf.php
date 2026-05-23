<style>
    body {
        font-family: 'Free Sans', sans-serif;
    }

    .table-pdf {
        width: 100%;
        border-collapse: collapse;
        page-break-inside: auto;
    }

    .table-pdf th,
    .table-pdf td {
        border: 1px solid black;
        padding: 8px;
        font-size: 15px;
    }

    .table-pdf tr {
        page-break-inside: avoid;
        page-break-after: auto;
    }

    .table-pdf thead {
        display: table-header-group;
    }

    .table-pdf tfoot {
        display: table-footer-group;
    }
</style>
<table width="100%">
    <tr>
        <td style="text-align: center;" colspan="2">
            <div style="color:black; font-size:20px; font-weight:bold;">PAYMENT RECEIPT</div>
            <div><b style="color:#4e4e4e;">RECEIPT ID #<?= $payment->paymentid ?></b></div>
        </td>
    </tr>
    <tr>
        <td style="text-align: left;">
            <div>
                <div><b>From :</b></div>
                <?= format_organization_info(); ?>
            </div>
        </td>
        <td style="text-align: right;">
            <div>
                <div><b>Bill To :</b></div>
                <?php if ($payment->invoiceid == "0") { ?>
                    <?= format_proposal_info($payment->proposal_id); ?>
                <?php } else { ?>
                    <?= format_customer_info($payment->invoice_data, 'payment', 'billing'); ?>
                <?php } ?>
            </div>
        </td>
    </tr>
</table>
<br /><br />
<table class="table-pdf" width="100%">
    <tr>
        <td style="text-align: center;" colspan="2"><strong> Payment Details</strong></td>
    </tr>
    <tr>
        <td><strong><?= _l('payment_date'); ?></strong></td>
        <td><?= _d($payment->date) ?></td>
    </tr>
    <tr>
        <td><strong><?= _l('payment_view_mode'); ?></strong></td>
        <td>
            <?php
            $payment_name = $payment->name;
            if (!empty($payment->paymentmethod)) {
                $payment_name .= ' - ' . $payment->paymentmethod;
            }
            ?>
            <?= $payment_name; ?>
        </td>
    </tr>
    <tr>
        <td><strong><?= _l('payment_transaction_id'); ?></strong></td>
        <td><?= $payment->transactionid; ?></td>
    </tr>
    <tr>
        <td><strong><?= _l('payment_total_amount'); ?></strong></td>
        <?php if ($payment->invoiceid == "0") { ?>
            <td><?= app_format_money($payment->amount, $proposalData->currency); ?></td>
        <?php } else { ?>
            <td><?= app_format_money($payment->amount, $payment->invoice_data->currency_name); ?></td>
        <?php } ?>
    </tr>
</table>
<br /><br />
<?php if ($payment->invoiceid == "0") { ?>
    <table class="table-pdf" width="100%">
        <tr>
            <td style="text-align: center;" colspan="2"><strong> Payment For Proposal</strong></td>
        </tr>
        <tr>
            <td><strong>Proposal Number</strong></td>
            <td><?= format_proposal_number($payment->proposal_id) ?></td>
        </tr>
        <tr>
            <td><strong>Proposal Date</strong></td>
            <td><?= _d($proposalData->date); ?></td>
        </tr>
        <tr>
            <td><strong>Total Amount</strong></td>
            <td><?= app_format_money($proposalData->total, $proposalData->currency); ?></td>
        </tr>
        <tr>
            <td><strong>Payment Amount</strong></td>
            <td><?= app_format_money($payment->amount, $proposalData->currency); ?></td>
        </tr>
        <?php if ($proposalPaymentData['remaining_amount'] > 0) { ?>
            <tr>
                <td><strong>Amount Due</strong></td>
                <td style="color:red;"><?= app_format_money($proposalPaymentData['remaining_amount'], $proposalData->currency); ?></td>
            </tr>
        <?php } ?>
    </table>
<?php } else { ?>
    <table class="table-pdf" width="100%">
        <tr>
            <td style="text-align: center;" colspan="2"><strong> Payment For Invoice</strong></td>
        </tr>
        <tr>
            <td><strong><?= _l('payment_table_invoice_number'); ?></strong></td>
            <td><?= format_invoice_number($payment->invoice_data->id) ?></td>
        </tr>
        <tr>
            <td><strong><?= _l('payment_table_invoice_date'); ?></strong></td>
            <td><?= _d($payment->invoice_data->date); ?></td>
        </tr>
        <tr>
            <td><strong><?= _l('payment_table_invoice_amount_total'); ?></strong></td>
            <td><?= app_format_money($payment->invoice_data->total, $payment->invoice_data->currency_name); ?></td>
        </tr>
        <tr>
            <td><strong><?= _l('payment_table_payment_amount_total'); ?></strong></td>
            <td><?= app_format_money($payment->amount, $payment->invoice_data->currency_name); ?></td>
        </tr>
        <?php if ($amountDue) { ?>
            <tr>
                <td><strong><?= _l('invoice_amount_due'); ?></strong></td>
                <td style="color:red;"><?= app_format_money($payment->invoice_data->total_left_to_pay, $payment->invoice_data->currency_name); ?></td>
            </tr>
        <?php } ?>
    </table>
<?php } ?>