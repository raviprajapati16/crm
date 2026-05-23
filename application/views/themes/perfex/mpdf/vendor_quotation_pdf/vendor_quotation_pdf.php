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
        padding: 6px;
        font-size: 14px;
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
<div>
    <div style="text-align: center; font-size: 22px; font-weight: bold;">Vendor Quotation</div>
</div>
<br>
<table class="table-pdf">
    <tr>
        <td><strong>Supplier Name</strong></td>
        <td style='text-align: left;'><?= $form_data['supplier_name'] ?></td>
    </tr>
    <tr>
        <td><strong>GST IN</strong></td>
        <td style='text-align: left;'><?= $form_data['gst_in'] ?></td>

    </tr>
    <tr>
        <td><strong>Address</strong></td>
        <td><?= $form_data['address'] ?></td>
    </tr>
    <tr>
        <td><strong>Date</strong></td>
        <td style='text-align: left;'><?= _d($form_data['quotation_date']) ?></td>
    </tr>
</table>
<br /><br />
<table class="table-pdf">
    <thead>
        <tr>
            <th width="7%"><strong>Sr. No.</strong></th>
            <th><strong>Description Of Service</strong></th>
            <th><strong>HSN / SAC</strong></th>
            <th><strong>Quantity</strong></th>
            <th><strong>Unit</strong></th>
            <th><strong>Price</strong></th>
            <th><strong>Amount</strong></th>
        </tr>
    </thead>
    <tbody>
        <?php
        if (!empty($item_data)) {
            foreach ($item_data as $key => $item) {
        ?>
                <tr>
                    <td><?= ($key + 1) ?></td>
                    <td><?= $item['service_description'] ?></td>
                    <td><?= $item['hsn_sac'] ?></td>
                    <td><?= $item['qty'] ?></td>
                    <td><?= $item['unit'] ?></td>
                    <td><?= app_format_money($item['price_in_inr'], 'INR'); ?></td>
                    <td><?= app_format_money($item['amount_in_inr'], 'INR'); ?></td>
                </tr>
        <?php
            }
        }
        ?>
    </tbody>
</table>
<br />
<?php
if (!empty($form_data['terms_conditions'])) {
?>
    <div>
        <div style="font-weight: bold; border-bottom:1px solid black;">Terms & Conditions</div>
        <div><?= $form_data['terms_conditions'] ?></div>
    </div>
<?php
}
?>
<?php
if (!empty($form_data['notes'])) {
?>
    <br />
    <div>
        <div style="font-weight: bold;  border-bottom:1px solid black;">Notes</div>
        <div><?= $form_data['notes'] ?></div>
    </div>
<?php
}
?>
<br /><br /><br />
<?php
$sigantureText = "";
$staffData = get_staff($form_data['created_by']);
if (!empty($staffData)) {
    if (!empty($staffData->email_signature)) {
?>
        <div style="text-align:left"><?= $staffData->email_signature ?></div>
<?php
    }
}
?>