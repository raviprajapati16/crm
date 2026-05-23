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
<div style="font-size: 16px; font-weight:bold; margin-bottom:10px;">Payment Terms : </div>
<table class="table-pdf">
    <tr>
        <td width="10%"><strong>Sr. No.</strong></td>
        <td width="30%"><strong>Amount %</strong></td>
        <td width="20%"><strong>Amount</strong></td>
        <td width="15%"><strong>Payment Due Date</strong></td>
        <td width="25%"><strong>Notes</strong></td>
    </tr>
    <?php
    $no = 1;
    foreach ($paymentData as $item) {
    ?>
        <tr>
            <td><?= $no ?></td>
            <td><?= $item['percentage'] ?>% of the Total Project / contract / purchase Value</td>
            <td><?= app_format_money($item['amount'], get_contract_currency($item['contract_id']), false) . '/-'; ?></td>
            <td><?= _d($item['scheduled_payment_date']); ?></td>
            <td><?= $item['note']; ?></td>
        </tr>
    <?php
        $no++;
    }
    ?>
</table>