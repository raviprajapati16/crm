<style>
    body {
        font-family: 'Free Sans', sans-serif;
    }
</style>
<table width="100%">
    <tr>
        <td style="text-align: center;" colspan="2">
            <div style="color:black; font-size:20px; font-weight:bold;">Debit Note</div>
            <div><b style="color:#4e4e4e;">#<?= format_debit_note_number($debit_note->id) ?></b></div>

            <?php if (get_option('show_status_on_pdf_ei') == 1) { ?>
                <span style="color:rgb(<?= debit_note_status_color_pdf($debit_note->status) ?>);text-transform:uppercase;">
                    <?= format_debit_note_status($debit_note->status, true) ?>
                </span>
            <?php } ?>


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
                <?= format_customer_info($debit_note, 'debit_note', 'billing'); ?>
            </div>
            <?php if ($debit_note->include_shipping == 1 && $debit_note->show_shipping_on_debit_note == 1) { ?>
                <br>
                <div>
                    <div><b>Ship To :</b></div>
                    <?= format_customer_info($debit_note, 'debit_note', 'shipping'); ?>
                </div>
            <?php } ?>
            <br>
            <div>Debit Note Date: <?= _d($debit_note->date) ?></div>

            <?php if (!empty($debit_note->reference_no)) { ?>
                <div>Reference No. #: <?= $debit_note->reference_no ?></div>
            <?php } ?>

            <?php if ($debit_note->project_id != 0 && get_option('show_project_on_debit_note') == 1) { ?>
                <div>Project: <?= get_project_name_by_id($debit_note->project_id) ?></div>
            <?php } ?>

            <?php
            $pdf_custom_fields = get_custom_fields('debit_note', array('show_on_pdf' => 1));
            foreach ($pdf_custom_fields as $field) {
                $value = get_custom_field_value($debit_note->id, $field['id'], 'invoice');
                if ($value == '') {
                    continue;
                }
            ?>
                <div><?= $field['name'] . ': ' . $value ?></div>
            <?php
            }
            ?>
        </td>
    </tr>
</table>

<?php
$CI = &get_instance();
?>
<style>
    .table-pdf {
        width: 100%;
        border-collapse: collapse;
        page-break-inside: auto;
    }

    .table-pdf th,
    .table-pdf td {
        border: 1px solid black;
        padding: 8px;
        font-size: 12px;
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
<br />
<table class="table-pdf">
    <thead>
        <tr>
            <th>#</th>
            <th>Item</th>
            <th>Capacity</th>
            <th>Qty.</th>
            <th>Rate</th>
            <th>Tax</th>
            <th>Amount</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $i = 1;
        foreach ($debit_note->items as $key => $item) {
            $total = $item['rate'] * $item['qty'];
            $taxArr = get_debit_note_item_taxes($item['id']);
            $taxHtml = "";
            if (!empty($taxArr)) {
                foreach ($taxArr as $key => $txitem) {
                    $taxHtml .= implode(" ", explode("|", $txitem['taxname'])) . "%<br>";
                }
            }
        ?>
            <tr>
                <td><?= $i ?></td>
                <td>
                    <b><?= $item['description']; ?></b>
                    <br>
                    <?= $item['long_description']; ?>
                </td>
                <td><?= $item['capacity']; ?></td>
                <td><?= floatVal($item['qty']);
                    if ($item['unit']) {
                        echo " " . $item['unit'];
                    }
                    ?>
                </td>
                <td><?= app_format_money($item['rate'], 'INR', true) . '/-'; ?></td>
                <td><?= $taxHtml ?></td>
                <td><?= app_format_money($total, 'INR', true) . '/-'; ?></td>
            </tr>
        <?php
            $i++;
        } ?>
    </tbody>
</table>
<br />
<table class="table-pdf">
    <tr>
        <td colspan="3"><strong>Sub Total</strong></td>
        <td colspan="3"><?= app_format_money($debit_note->subtotal, $debit_note->currency_name) ?></td>
    </tr>
    <?php
    if (is_sale_discount_applied($debit_note)) {
    ?>
        <tr>
            <td colspan="3"><strong>
                    <?php echo "Discount";
                    if (is_sale_discount($debit_note, 'percent')) {
                        echo '(' . app_format_number($debit_note->discount_percent, true) . '%)';
                    }
                    ?>
                </strong>
            </td>
            <td colspan="3">- <?= app_format_money($debit_note->discount_total, $debit_note->currency_name) ?></td>
        </tr>';
    <?php
    }
    ?>
    <?php
    $taxArr = get_items_table_data($debit_note, 'debit_note', 'pdf')->taxes();
    if (!empty($taxArr)) {
        foreach ($taxArr as $key => $tax) {
    ?>
            <tr>
                <td colspan="3"><strong><?= $tax['taxname']  ?> (<?= $tax['taxrate'] ?>%)</strong></td>
                <td colspan="3"><?= app_format_money($tax['total_tax'], $debit_note->currency_name) . '/-'; ?></td>
            </tr>
    <?php
        }
    }
    ?>
    <?php if ((int)$debit_note->adjustment != 0) { ?>
        <tr>
            <td colspan="3"><strong>Adjustment</strong></td>
            <td colspan="3"><?= app_format_money($debit_note->adjustment, $debit_note->currency_name) ?></td>
        </tr>
    <?php } ?>
    <tr>
        <td style="background-color: #e6e5e3;" colspan="3"><strong>Total</strong></td>
        <td style="background-color: #e6e5e3;" colspan="3"><?= app_format_money($debit_note->total, $debit_note->currency_name) ?></td>
    </tr>


    <?php if ($debit_note->debits_used) { ?>
        <tr>
            <td colspan="3"><strong>Debit Used</strong></td>
            <td colspan="3">- <?= app_format_money($debit_note->debits_used, $debit_note->currency_name) ?></td>
        </tr>
    <?php } ?>

    <?php if ($debit_note->total_refunds) { ?>
        <tr>
            <td colspan="3"><strong>Refund</strong></td>
            <td colspan="3">- <?= app_format_money($debit_note->total_refunds, $debit_note->currency_name) ?></td>
        </tr>
    <?php } ?>

    <tr>
        <td colspan="3"><strong>Remaining Debit</strong></td>
        <td colspan="3"><?= app_format_money($debit_note->remaining_debits, $debit_note->currency_name) ?></td>
    </tr>
    </tbody>
</table>
<?php
if (get_option('total_to_words_enabled') == 1) {
    echo '<br>';
    echo '<strong style="text-align:center;"> With Words : ' . strtoupper(convertNumberToWords($debit_note->total, $debit_note->currency))  . '</strong>';
}
?>

<?php
if (!empty($debit_note->clientnote)) {
?>
    <br /><br />
    <div><b><?= _l('note'); ?> :</b></div>
    <div><?= $debit_note->clientnote ?></div>
<?php
}

if (!empty($debit_note->terms)) {
?>
    <br /><br />
    <div><b><?= _l('terms_and_conditions'); ?> :</b></div>
    <div><?= $debit_note->terms ?></div>
<?php
}
?>

<?php
$company_signature = get_option('signature_image');
?>
<br /><br />
<table style="width: 100%; vertical-align: top;">
    <tr>
        <td style="text-align: left; vertical-align: top;">
            <div><b>Authorized Signature</b></div><br />
            <?php if(!empty($company_signature)) { ?>
            <div><img width="220px" src="<?php echo base_url('uploads/company/' . $company_signature); ?>" style="display: block;"></div>
            <?php } ?>
        </td>
    </tr>
</table>