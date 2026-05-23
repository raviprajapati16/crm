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
<br />
<table width="100%">
    <tr>
        <td style="text-align: left; vertical-align: top;" width="45%">
            <div>
                <div><b>To :</b></div>
                <?= format_customer_info($statement['client'], 'statement', 'billing'); ?>
            </div>
        </td>
        <td style="text-align: right; vertical-align: top;">
            <div>
                <div><b style="font-size: 22px;"><?= _l('account_summary') ?></b></div>
                <div style="color:#676767; font-size: 14px;"><?= _l('statement_from_to', [_d($statement['from']), _d($statement['to']),]) ?></div>
                <table style="border-top: 1px solid black; margin-top:10px;" width="100%">
                    <tbody>
                        <tr>
                            <td align="left"><?= _l('statement_beginning_balance') ?></td>
                            <td><?= app_format_money($statement['beginning_balance'], $statement['currency']) ?></td>
                        </tr>
                        <tr>
                            <td align="left"><?= _l('invoiced_amount') ?>:</td>
                            <td><?= app_format_money($statement['invoiced_amount'], $statement['currency']) ?></td>
                        </tr>
                        <tr>
                            <td align="left"><?= _l('amount_paid') ?>:</td>
                            <td><?= app_format_money($statement['amount_paid'], $statement['currency']) ?></td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td align="left"><b><?= _l('balance_due') ?></b>:</td>
                            <td><?= app_format_money($statement['balance_due'], $statement['currency']) ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </td>
    </tr>
</table>
<br /><br />
<div style="text-align: center;">
    <?= _l('customer_statement_info', [
        _d($statement['from']),
        _d($statement['to']),
    ]); ?>
</div>
<br /><br />
<?php

$tmpBeginningBalance = $statement['beginning_balance'];

$tblhtml = '<table width="100%" class="table-pdf">
<thead>
 <tr height="10" bgcolor="#e8e8e8" style="color:#424242;">
     <th align="center" width="15%"><b>' . _l('statement_heading_date') . '</b></th>
     <th align="center" width="27%"><b>' . _l('statement_heading_details') . '</b></th>
     <th align="center"><b>' . _l('statement_heading_amount') . '</b></th>
     <th align="center"><b>' . _l('statement_heading_payments') . '</b></th>
     <th align="center"><b>' . _l('statement_heading_balance') . '</b></th>
 </tr>
</thead>
<tbody>
 <tr>
     <td width="15%">' . _d($statement['from']) . '</td>
     <td width="27%">' . _l('statement_beginning_balance') . '</td>
     <td align="right">' . app_format_money($statement['beginning_balance'], $statement['currency'], true) . '</td>
     <td></td>
     <td align="right">' . app_format_money($statement['beginning_balance'], $statement['currency'], true) . '</td>
 </tr>';
$count = 0;
foreach ($statement['result'] as $data) {
    $tblhtml .= '<tr>
  <td width="15%">' . _d($data['date']) . '</td>
  <td width="27%">';
    if (isset($data['invoice_id'])) {
        $tblhtml .= _l('statement_invoice_details', [
            format_invoice_number($data['invoice_id']),
            _d($data['duedate']),
        ]);
    } elseif (isset($data['payment_id'])) {
        $tblhtml .= _l('statement_payment_details', [
            '#' . $data['payment_id'],
            format_invoice_number($data['payment_invoice_id']),
        ]);
    } elseif (isset($data['credit_note_id'])) {
        $tblhtml .= _l('statement_credit_note_details', format_credit_note_number($data['credit_note_id']));
    } elseif (isset($data['credit_id'])) {
        $tblhtml .= _l('statement_credits_applied_details', [
            format_credit_note_number($data['credit_applied_credit_note_id']),
            app_format_money($data['credit_amount'], $statement['currency'], true),
            format_invoice_number($data['credit_invoice_id']),
        ]);
    } elseif (isset($data['credit_note_refund_id'])) {
        $tblhtml .= _l('statement_credit_note_refund', format_credit_note_number($data['refund_credit_note_id']));
    }

    $tblhtml .= '</td>
    <td align="right">';
    if (isset($data['invoice_id'])) {
        $tblhtml .= app_format_money($data['invoice_amount'], $statement['currency'], true);
    } elseif (isset($data['credit_note_id'])) {
        $tblhtml .= app_format_money($data['credit_note_amount'], $statement['currency'], true);
    }
    $tblhtml .= '</td>
        <td align="right">';
    if (isset($data['payment_id'])) {
        $tblhtml .= app_format_money($data['payment_total'], $statement['currency'], true);
    } elseif (isset($data['credit_note_refund_id'])) {
        $tblhtml .= app_format_money($data['refund_amount'], $statement['currency'], true);
    }
    $tblhtml .= '</td>
            <td align="right">';
    if (isset($data['invoice_id'])) {
        $tmpBeginningBalance = ($tmpBeginningBalance + $data['invoice_amount']);
    } elseif (isset($data['payment_id'])) {
        $tmpBeginningBalance = ($tmpBeginningBalance - $data['payment_total']);
    } elseif (isset($data['credit_note_id'])) {
        $tmpBeginningBalance = ($tmpBeginningBalance - $data['credit_note_amount']);
    } elseif (isset($data['credit_note_refund_id'])) {
        $tmpBeginningBalance = ($tmpBeginningBalance + $data['refund_amount']);
    }
    if (!isset($data['credit_id'])) {
        $tblhtml .= app_format_money($tmpBeginningBalance, $statement['currency'], true);
    }

    $tblhtml .= '</td>
            </tr>';
}
$tblhtml .= '</tbody>
        <tfoot>
         <tr style="color:#424242;">
             <td colspan="4" align="right"><b>' . _l('balance_due') . '</b></td>
             <td align="right">
                 <b>' . app_format_money($statement['balance_due'], $statement['currency']) . '</b>
             </td>
         </tr>
     </tfoot>
 </table>';
echo $tblhtml;
?>