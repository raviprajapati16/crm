<!DOCTYPE html>
<html>
<?php
$currencyData = get_currency($purchase->currency);
$getTax = get_tax_by_relation($purchase->id, "purchase");
$applied_debits = 0;
$applied_debits_data = $this->debit_notes_model->get_applied_purchase_debits($purchase->id);
if (!empty($applied_debits_data)) {
    foreach ($applied_debits_data as $key => $item) {
        $applied_debits += $item['amount'];
        $purchase->total += $item['amount'];
    }
}
?>

<head>
    <meta charset="UTF-8">
    <title>Purchase Order</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            margin: 0;
            padding: 10px;
            line-height: 1.2;
        }

        .purchase-container {
            width: 100%;
            border-collapse: collapse;
            border: 2px solid #000;
        }

        .header-title {
            background-color: #ffffff;
            font-weight: bold;
            font-size: 14px;
            text-align: center;
            padding: 8px;
            border-bottom: 1px solid #000;
        }

        .info-row {
            border-bottom: 1px solid #000;
        }

        .info-cell {
            padding: 4px 6px;
            border: 1px solid #000;
            vertical-align: top;
        }

        .info-cell:last-child {
            border-right: none;
        }

        .label {
            font-weight: bold;
            background-color: #f5f5f5;
        }

        .product-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 2px;
        }

        .product-header {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
            padding: 4px;
            border: 1px solid #000;
        }

        .product-cell {
            padding: 3px 4px;
            border: 1px solid #000;
            text-align: center;
        }

        .product-cell.left {
            text-align: left;
        }

        .amount-row {
            background-color: #f9f9f9;
        }

        .total-row {
            background-color: #e6e6e6;
            font-weight: bold;
        }

        .bank-details {
            width: 100%;
            border-collapse: collapse;
            margin-top: 2px;
        }

        .bank-cell {
            padding: 3px 6px;
            border: 1px solid #000;
            vertical-align: top;
        }

        .signature-section {
            width: 100%;
            border-collapse: collapse;
            margin-top: 2px;
            border: 1px solid #000;
        }

        .signature-cell {
            padding: 15px 6px;
            vertical-align: top;
            height: 120px;
        }

        .page-number {
            text-align: center;
            font-weight: bold;
            padding: 4px;
            border-top: 1px solid #000;
        }

        /* New page styles for mPDF */
        .new-page {
            page-break-before: always;
        }
    </style>
</head>

<body>
    <table class="purchase-container">
        <!-- Header -->
        <tr>
            <td colspan="4" class="header-title">Purchase Order</td>
        </tr>
        <tr>
            <td class="info-cell label" colspan="2">Purchase Order No. :
                <?= $purchase->purchase_number_prefix . $purchase->purchase_number ?>
            </td>
            <td class="info-cell label" colspan="2">Purchase Order Date : <?= _d($purchase->date); ?></td>
        </tr>

        <!-- Seller and Buyer Info -->
        <tr class="info-row">
            <td class="info-cell label" colspan="2">Seller:</td>
            <td class="info-cell label" colspan="2">Buyer:</td>
        </tr>
        <tr class="info-row">
            <td class="info-cell" colspan="2">
                <div class="label"><?= $purchase->purchase_to ?></div>
                <div><?= $purchase->address ?></div>
                <div><?= $purchase->city ?>, <?= $purchase->state ?></div>
                <div><?= get_country_name($purchase->country) ?> <?= $purchase->zip ?></div>
                <?php if (!empty($purchase->phone)) { ?>
                    <div>Contact No - <?= $purchase->phone ?></div>
                <?php } ?>
                <?php if (!empty($purchase->email)) { ?>
                    <div>Email - <?= $purchase->email ?></div>
                <?php } ?>
                <?php
                $gst_no = "";
                if (isset($purchase)) {
                    $rel_data = get_relation_data('vendor', $purchase->vendor_id);
                    $gst_no = $rel_data->gst_in;
                    if (!empty($gst_no)) {
                        echo '<div>GST IN - ' . $gst_no . '</div>';
                    }
                }
                ?>
            </td>
            <td class="info-cell" colspan="2">
                <?= format_organization_info(true); ?>
            </td>
        </tr>

        <tr class="info-row">
            <td class="info-cell label " width="25%">Place of Loading:</td>
            <td class="info-cell label " width="25%">Place of Discharge:</td>
            <td class="info-cell label " width="25%">Payment Term:</td>
            <td class="info-cell label " width="25%">Shipment Term:</td>
        </tr>
        <tr class="info-row">
            <td class="info-cell"><?= $purchase->loading_place ?></td>
            <td class="info-cell"><?= $purchase->discharge_place ?></td>
            <td class="info-cell"><?= $purchase->payment_term ?></td>
            <td class="info-cell"><?= $purchase->shipment_term ?></td>
        </tr>
    </table>

    <!-- Product Details -->
    <?php
    $qtyunit = "";
    if (isset($purchase->items[0]['unit'])) {
        $qtyunit = $purchase->items[0]['unit'];
    }
    ?>
    <table class="product-table">
        <tr>
            <td class="product-header" style="width: 6%;"><span class="">Sr. No.</span></td>
            <td class="product-header" style="width: 30%;" colspan="2"><span class="">Product Name & Description</span>
            </td>
            <td class="product-header" style="width: 12%;"><span class="">HS Code</span></td>
            <td class="product-header" style="width: 10%;"><span class="">Quantity<br>(<?= $qtyunit ?>)</span></td>
            <td class="product-header" style="width: 17%;"><span class="">Amount/Unit<br>(In
                    <?= $currencyData->name ?>)</span></td>
            <td class="product-header" style="width: 17%;"><span class="">Total Amount<br>(In
                    <?= $currencyData->name ?>)</span></td>
        </tr>

        <?php
        $totalQty = 0;
        $no = 0;
        foreach ($purchase->items as $key => $item) {
            $total = $item['rate'] * $item['qty'];
            $totalQty += $item['qty'];
        ?>
            <tr>
                <td class="product-cell"><?= $no += 1; ?></td>
                <td class="product-cell left" colspan="2">
                    <?= $item['description']; ?><br>
                    <small><?= $item['long_description']; ?></small>
                </td>
                <td class="product-cell"><?= $item['hsn_code']; ?></td>
                <td class="product-cell"><?= (int) $item['qty']; ?></td>
                <td class="product-cell"><?= number_format($item['rate'], 2, '.', ''); ?></td>
                <td class="product-cell"><?= number_format($total, 2, '.', ''); ?></td>
            </tr>

        <?php } ?>

        <tr class="amount-row">
            <td class="product-cell" colspan="6" style="text-align: right; font-weight: bold;">Sub Total Amount</td>
            <td class="product-cell" style="font-weight: bold;"><?= number_format($purchase->subtotal, 2, '.', ''); ?>
            </td>
        </tr>
        <?php
        if (is_sale_discount_applied($purchase) && $purchase->discount_type == 'before_tax') {
        ?>
            <tr class="amount-row">
                <td class="product-cell" colspan="6" style="text-align: right; font-weight: bold;">
                    <?php echo _l('estimate_discount');
                    if (is_sale_discount($purchase, 'percent')) {
                        echo ' (' . app_format_number($purchase->discount_percent, true) . '%)';
                    }
                    ?>
                </td>
                <td class="product-cell" style="font-weight: bold;"> -
                    <?= number_format($purchase->discount_total, 2, '.', ''); ?>
                </td>
            </tr>
        <?php
        }
        ?>
        <?php
        $dynamicAmounts = get_dynamic_amount_fields("purchase", $purchase->id);
        if (!empty($dynamicAmounts)) {
            foreach ($dynamicAmounts as $key => $item) {
        ?>
                <tr class="amount-row">
                    <td class="product-cell" colspan="6" style="text-align: right;"><?= $item['label'] ?></td>
                    <td class="product-cell"><?= number_format($item['amount'], 2, '.', ''); ?></td>
                </tr>
            <?php
            }
            ?>
        <?php } ?>

        <?php if (!empty($getTax)) { ?>
            <?php if ($getTax->taxrate != 0) { ?>
                <tr class="amount-row">
                    <td class="product-cell" colspan="6" style="text-align: right;"><strong>Taxable Amount</strong></td>
                    <td class="product-cell"><?= number_format($purchase->taxable_amount, 2, '.', ''); ?></td>
                </tr>
                <tr class="amount-row">
                    <td class="product-cell" colspan="6" style="text-align: right;"><?= $getTax->taxname ?>
                        (<?= $getTax->taxrate ?>%)</td>
                    <td class="product-cell"><?= number_format($purchase->total_tax, 2, '.', ''); ?></td>
                </tr>
            <?php } ?>
        <?php } ?>

        <?php
        if (is_sale_discount_applied($purchase) && $purchase->discount_type == 'after_tax') {
        ?>
            <tr class="amount-row">
                <td class="product-cell" colspan="6" style="text-align: right; font-weight: bold;">
                    <?php echo _l('estimate_discount');
                    if (is_sale_discount($purchase, 'percent')) {
                        echo ' (' . app_format_number($purchase->discount_percent, true) . '%)';
                    }
                    ?>
                </td>
                <td class="product-cell" style="font-weight: bold;"> -
                    <?= number_format($purchase->discount_total, 2, '.', ''); ?>
                </td>
            </tr>
        <?php
        }
        ?>
        <?php if ($purchase->adjustment > 0) { ?>
            <tr class="amount-row">
                <td class="product-cell" colspan="6" style="text-align: right;"><?= _l('estimate_adjustment') ?></td>
                <td class="product-cell"><?= number_format($purchase->adjustment, 2, '.', ''); ?></td>
            </tr>
        <?php } ?>

        <?php if ($applied_debits > 0) { ?>
            <tr class="amount-row">
                <td class="product-cell" colspan="6" style="text-align: right; font-weight: bold;">Applied Debits</td>
                <td class="product-cell" style="font-weight: bold;"> + <?= number_format($applied_debits, 2, '.', ''); ?>
                </td>
            </tr>
        <?php } ?>

        <tr class="total-row">
            <td class="product-cell" colspan="4" style="text-align: left; font-weight: bold;">
                <span class="">Amount In Word:</span>
                <?= strtoupper(convertNumberToWords($purchase->total, $purchase->currency)) ?>
            </td>
            <td class="product-cell" style="font-weight: bold; font-size: 12px;"><?= (int) $totalQty; ?></td>
            <td class="product-cell" style="font-weight: bold; font-size: 12px;">Total Amount</td>
            <td class="product-cell" style="text-align: center; font-weight: bold; font-size: 12px;">
                <?= number_format($purchase->total, 2, '.', ''); ?>
            </td>
        </tr>
    </table>

    <!-- Banking Details -->
    <table class="bank-details">
        <tr>
            <td class="bank-cell" colspan="2" rowspan="4"><strong>Notes:</strong> <?= $purchase->notes ?></td>
            <td class="bank-cell label" colspan="2">Company Details:</td>
        </tr>
        <tr>
            <td class="bank-cell" width="25%">GST Number:</td>
            <td class="bank-cell" width="25%"><?= get_option('company_vat') ?></td>
        </tr>
        <tr>
            <td class="bank-cell">PAN/IEC Number:</td>
            <td class="bank-cell"><?= get_option('company_pan_number') ?></td>
        </tr>
        <tr>
            <td class="bank-cell">TAN Number:</td>
            <td class="bank-cell"><?= get_option('company_tan_number') ?></td>
        </tr>
    </table>
    <!-- Signature Section -->
    <?php
    $company_signature_path = 'uploads/company/' . get_option('signature_image');
    ?>
    <table class="signature-section">
        <tr>
            <td class="signature-cell" style="height: 100%;">
                <?= $purchase->content ?>
            </td>
        </tr>
        <tr>
            <td colspan="2" class="signature-cell" style="text-align:right;  vertical-align: bottom;">
                For, <?= get_option('invoice_company_name') ?><br>
                <?php
                if (file_exists($company_signature_path) && !empty(get_option('signature_image'))) {
                ?>
                    <div style="text-align: right">
                        <img src="<?= base_url('uploads/company/' . get_option('signature_image')) ?>"
                            alt="Company Signature" width="140" height="130">
                    </div>

                <?php
                }
                ?>
                <!-- <strong>Name of Signing Authority:</strong> <?= get_staff_full_name($purchase->assigned); ?><br>
                 <strong>Designation: </strong><?= get_staff_designation($purchase->assigned) ?>
                 <br> -->
                (Authorised Signatory)
            </td>
        </tr>
    </table>


</body>

</html>