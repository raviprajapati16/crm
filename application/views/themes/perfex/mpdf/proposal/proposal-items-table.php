<?php
$CI = &get_instance();
$number_word_lang_rel_id = 'unknown';
if ($proposal->rel_type == 'customer') {
    $number_word_lang_rel_id = $proposal->rel_id;
}
$this->load->library('app_number_to_word', [
    'clientid' => $number_word_lang_rel_id,
], 'numberword');
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
<table class="table-pdf">
    <thead>
        <tr>
            <th>#</th>
            <th>Item</th>
            <th>HSN Code</th>
            <!-- <th>Capacity</th> -->
            <th>Qty.</th>
            <th>Rate</th>
            <th>Amount</th>
        </tr>
    </thead>
    <tbody>
        <?php
        foreach ($proposal->items as $key => $item) {
            $total = $item['rate'] * $item['qty'];
        ?>
            <tr>
                <td><?= $key + 1 ?></td>
                <td>
                    <b><?= $item['description']; ?></b>
                    <br>
                    <?= $item['long_description']; ?>
                </td>
                <td><?= $item['hsn_code']; ?></td>
                <!-- <td><?= $item['capacity']; ?></td> -->
                <td><?= floatVal($item['qty']);
                    if ($item['unit']) {
                        echo " " . $item['unit'];
                    }
                    ?>
                </td>
                <td><?= app_format_money($item['rate'], 'INR', true) . '/-'; ?></td>
                <td><?= app_format_money($total, 'INR', true) . '/-'; ?></td>
            </tr>
        <?php } ?>
    </tbody>
</table>
<br>
<table class="table-pdf">
    <tr>
        <td colspan="3"><strong><?= _l('estimate_subtotal') ?> </strong></td>
        <td colspan="3"><?= app_format_money($proposal->subtotal, $proposal->currency_name) ?></td>
    </tr>
    <?php
    if (is_sale_discount_applied($proposal)) {
    ?>
        <tr>
            <td colspan="3"><strong>
                    <?php echo _l('estimate_discount');
                    if (is_sale_discount($proposal, 'percent')) {
                        echo '(' . app_format_number($proposal->discount_percent, true) . '%)';
                    }
                    ?>
                </strong>
            </td>
            <td colspan="3">- <?= app_format_money($proposal->discount_total, $proposal->currency_name) ?></td>
        </tr>';
    <?php
    }
    ?>
    <?php
    $taxArr = get_items_table_data($proposal, 'proposal', 'pdf')->taxes();
    if (!empty($taxArr)) {
        foreach ($taxArr as $key => $tax) {
    ?>
            <tr>
                <td colspan="3"><strong><?= $tax['taxname']  ?> (<?= $tax['taxrate'] ?>%)</strong></td>
                <td colspan="3"><?= app_format_money($tax['total_tax'], $proposal->currency_name) . '/-'; ?></td>
            </tr>
    <?php
        }
    }
    ?>
    <?php if ((int)$proposal->adjustment != 0) { ?>
        <tr>
            <td colspan="3"><strong><?= _l('estimate_adjustment') ?></strong></td>
            <td colspan="3"><?= app_format_money($proposal->adjustment, $proposal->currency_name) ?></td>
        </tr>
    <?php } ?>
    <tr>
        <td colspan="3"><strong><?= _l('estimate_total') ?></strong></td>
        <td colspan="3"><?= app_format_money($proposal->total, $proposal->currency_name) ?></td>
    </tr>
    </tbody>
</table>
<?php
if (get_option('total_to_words_enabled') == 1) {
    echo '<br>';
    echo '<strong style="text-align:center;">' . _l('num_word') . ' : ' . strtoupper(convertNumberToWords($proposal->total, $proposal->currency))  . '</strong>';
}
?>
