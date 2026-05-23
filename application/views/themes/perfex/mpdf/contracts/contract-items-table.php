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
    <tr>
        <td style="text-align:center;" colspan="8"><strong>Details & Specifications</strong></td>
    </tr>
    <tr>
        <td colspan="8"><strong>Site Location : </strong> <?= ucwords($proposal->city)  ?>, <?= ucwords($proposal->state) ?></td>
    </tr>
    <tr>
        <td colspan="4"><strong>Quotation No.</strong> <?= format_proposal_number($proposal->id);  ?></td>
        <td colspan="4"><strong>Quotation Date : </strong> <?= date('d-m-Y', strtotime($proposal->date)) ?></td>
    </tr>
    <tr>
        <th>#</th>
        <th>Product</th>
        <th>Model</th>
        <th>Item</th>
        <th>Capacity</th>
        <th>Qty.</th>
        <th>Rate</th>
        <th>Amount</th>
    </tr>
    <?php
    $main_tax_type = [];
    foreach ($proposal->items as $key => $item) {
        $total = $item['rate'] * $item['qty'];
        $taxArr = get_proposal_item_taxes($item['id']);
        if (!empty($taxArr)) {
            foreach ($taxArr as $tax) {
                $main_tax_type[] = str_replace("|", " ", $tax['taxname']) . '%';
            }
        }
    ?>
        <tr>
            <td><?= $key + 1 ?></td>
            <td><?= $item['main_group_name']; ?></td>
            <td><?= $item['sub_group_name']; ?></td>
            <td><?= $item['description']; ?></td>
            <td><?= $item['capacity']; ?></td>
            <td><?= floatVal($item['qty']);
                if ($item['unit']) {
                    echo " " . $item['unit'];
                }
                ?>
            </td>
            <td><?= app_format_money($item['rate'], $proposal->currency_name, true) . '/-'; ?></td>
            <td><?= app_format_money($total, $proposal->currency_name, true) . '/-'; ?></td>
        </tr>
    <?php } ?>
    <tr>
        <td colspan="4"><strong><?= _l('estimate_subtotal') ?> </strong></td>
        <td colspan="4"><?= app_format_money($proposal->subtotal, $proposal->currency_name, true) ?></td>
    </tr>
    <?php
    if (is_sale_discount_applied($proposal)) {
    ?>
        <tr>
            <td colspan="4"><strong>
                    <?php echo _l('estimate_discount');
                    if (is_sale_discount($proposal, 'percent')) {
                        echo '(' . app_format_number($proposal->discount_percent, true) . '%)';
                    }
                    ?>
                </strong>
            </td>
            <td colspan="4">- <?= app_format_money($proposal->discount_total, $proposal->currency_name, true) ?></td>
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
                <td colspan="4"><strong><?= $tax['taxname']  ?> (<?= $tax['taxrate'] ?>%)</strong></td>
                <td colspan="4"><?= app_format_money($tax['total_tax'], $proposal->currency_name, true) . '/-'; ?></td>
            </tr>
    <?php
        }
    }
    ?>
    <?php if ((int)$proposal->adjustment != 0) { ?>
        <tr>
            <td colspan="4"><strong><?= _l('estimate_adjustment') ?></strong></td>
            <td colspan="4"><?= app_format_money($proposal->adjustment, $proposal->currency_name, true) ?></td>
        </tr>
    <?php } ?>
    <tr>
        <td colspan="4"><strong>Total Project Value</strong></td>
        <td colspan="4"><?= app_format_money($proposal->total, $proposal->currency_name, true) . '/-'; ?></td>
    </tr>
</table>