<!DOCTYPE html>
<html>
<?php
$currencyData = get_currency($proposal->currency);
$getTax = get_tax_by_relation($proposal->id, "proposal");
?>

<head>
    <meta charset="UTF-8">
    <title>Sales Contract Cum Proforma Invoice</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            margin: 0;
            padding: 10px;
            line-height: 1.2;
        }

        .invoice-container {
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
        }

        .signature-cell {
            padding: 15px 6px;
            border: 1px solid #000;
            vertical-align: top;
            height: 80px;
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
    <table class="invoice-container">
        <!-- Header -->
        <tr>
            <td colspan="4" class="header-title">Sales Contract Cum Proforma Invoice</td>
        </tr>
        <tr>
            <td class="info-cell label" colspan="2">Contract No.
                <?= $proposal->proposal_number_prefix . $proposal->proposal_number ?>
            </td>
            <td class="info-cell label" colspan="2">Contract Date <?= _d($proposal->date); ?></td>
        </tr>

        <!-- Seller and Buyer Info -->
        <tr class="info-row">
            <td class="info-cell label" colspan="2">Seller:</td>
            <td class="info-cell label" colspan="2">Buyer:</td>
        </tr>
        <tr class="info-row">
            <td class="info-cell" colspan="2">
                <?= format_organization_info(); ?>
            </td>
            <td class="info-cell" colspan="2">
                <?= format_proposal_info($proposal, 'pdf'); ?>
            </td>
        </tr>

        <tr class="info-row">
            <td class="info-cell label " width="25%">Place of Loading:</td>
            <td class="info-cell label " width="25%">Place of Discharge:</td>
            <td class="info-cell label " width="25%">Payment Term:</td>
            <td class="info-cell label " width="25%">Shipment Term:</td>
        </tr>
        <tr class="info-row">
            <td class="info-cell"><?= $proposal->loading_place ?></td>
            <td class="info-cell"><?= $proposal->discharge_place ?></td>
            <td class="info-cell"><?= $proposal->payment_term ?></td>
            <td class="info-cell"><?= $proposal->shipment_term ?></td>
        </tr>
    </table>

    <!-- Product Details -->
    <?php
    $qtyunit = "";
    if (isset($proposal->items[0]['unit'])) {
        $qtyunit = $proposal->items[0]['unit'];
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
        foreach ($proposal->items as $key => $item) {
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
            <td class="product-cell" style="font-weight: bold;"><?= number_format($proposal->subtotal, 2, '.', ''); ?>
            </td>
        </tr>
        <?php
        if (is_sale_discount_applied($proposal) && $proposal->discount_type == 'before_tax') {
        ?>
            <tr class="amount-row">
                <td class="product-cell" colspan="6" style="text-align: right; font-weight: bold;">
                    <?php echo _l('estimate_discount');
                    if (is_sale_discount($proposal, 'percent')) {
                        echo ' (' . app_format_number($proposal->discount_percent, true) . '%)';
                    }
                    ?>
                </td>
                <td class="product-cell" style="font-weight: bold;"> -
                    <?= number_format($proposal->discount_total, 2, '.', ''); ?>
                </td>
            </tr>
        <?php
        }
        ?>
        <?php
        $dynamicAmounts = get_dynamic_amount_fields("proposal", $proposal->id);
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

        <?php if ($proposal->type == "0") { ?>
            <?php if (!empty($getTax)) { ?>
                <?php if ($getTax->taxrate != 0) { ?>
                    <tr class="amount-row">
                        <td class="product-cell" colspan="6" style="text-align: right;"><strong>Taxable Amount</strong></td>
                        <td class="product-cell"><?= number_format($proposal->taxable_amount, 2, '.', ''); ?></td>
                    </tr>
                    <tr class="amount-row">
                        <td class="product-cell" colspan="6" style="text-align: right;"><?= $getTax->taxname ?>
                            (<?= $getTax->taxrate ?>%)</td>
                        <td class="product-cell"><?= number_format($proposal->total_tax, 2, '.', ''); ?></td>
                    </tr>
                <?php } ?>
            <?php } ?>
        <?php } ?>
        <?php
        if (is_sale_discount_applied($proposal) && $proposal->discount_type == 'after_tax') {
        ?>
            <tr class="amount-row">
                <td class="product-cell" colspan="6" style="text-align: right; font-weight: bold;">
                    <?php echo _l('estimate_discount');
                    if (is_sale_discount($proposal, 'percent')) {
                        echo ' (' . app_format_number($proposal->discount_percent, true) . '%)';
                    }
                    ?>
                </td>
                <td class="product-cell" style="font-weight: bold;"> -
                    <?= number_format($proposal->discount_total, 2, '.', ''); ?>
                </td>
            </tr>
        <?php
        }
        ?>
        <?php if ($proposal->adjustment > 0) { ?>
            <tr class="amount-row">
                <td class="product-cell" colspan="6" style="text-align: right;"><?= _l('estimate_adjustment') ?></td>
                <td class="product-cell"><?= number_format($proposal->adjustment, 2, '.', ''); ?></td>
            </tr>
        <?php } ?>

        <tr class="total-row">
            <td class="product-cell" colspan="4" style="text-align: left; font-weight: bold;">
                <span class="">Amount In Word:</span>
                <?= strtoupper(convertNumberToWords($proposal->total, $proposal->currency)) ?>
            </td>
            <td class="product-cell" style="font-weight: bold; font-size: 12px;"><?= (int) $totalQty; ?></td>
            <td class="product-cell" style="font-weight: bold; font-size: 12px;">Total Amount</td>
            <td class="product-cell" style="text-align: center; font-weight: bold; font-size: 12px;">
                <?= number_format($proposal->total, 2, '.', ''); ?>
            </td>
        </tr>
    </table>

    <!-- Banking Details -->
    <table class="bank-details">
        <tr>
            <td class="bank-cell label " colspan="2" style="width: 12%;">Banking Details:</td>
            <td class="bank-cell label" colspan="2" style="width: 12%;">Company Details:</td>
        </tr>
        <tr>
            <td class="bank-cell" width="25%">Name:</td>
            <td class="bank-cell" width="25%"><?= $proposal->bank_ac_name ?></td>
            <td class="bank-cell" width="25%">GST Number:</td>
            <td class="bank-cell" width="25%"><?= get_option('company_vat') ?></td>
        </tr>
        <tr>
            <td class="bank-cell">Account No.:</td>
            <td class="bank-cell"><?= $proposal->bank_ac_no ?></td>
            <td class="bank-cell">PAN/IEC Number:</td>
            <td class="bank-cell"><?= get_option('company_pan_number') ?></td>
        </tr>
        <tr>
            <td class="bank-cell">Bank Name:</td>
            <td class="bank-cell"><?= $proposal->bank_name ?></td>
            <td class="bank-cell">TAN Number:</td>
            <td class="bank-cell"><?= get_option('company_tan_number') ?></td>
        </tr>
        <tr>
            <td class="bank-cell">IFSC Code:</td>
            <td class="bank-cell"><?= $proposal->bank_ifsc_code ?></td>
            <td class="bank-cell" colspan="2" rowspan="3"><strong>Notes:</strong> <?= $proposal->notes ?></td>
        </tr>
        <tr>
            <td class="bank-cell">Swift Code:</td>
            <td class="bank-cell"><?= $proposal->bank_swift_code ?></td>
        </tr>
        <tr>
            <td class="bank-cell">Address:</td>
            <td class="bank-cell"><?= $proposal->bank_address ?></td>
        </tr>
    </table>

    <!-- Signature Section -->
    <?php
    $company_signature_path = 'uploads/company/' . get_option('signature_image');
    $customer_sign_path = protected_file_url_by_path(get_upload_path_by_type('proposal') . $proposal->id . '/' . $proposal->signature);
    ?>
    <table class="signature-section">
        <tr>
            <td class="signature-cell" style="width: 50%;">
                <strong>The Seller:</strong><br>
                For and On Behalf of:<br><br>
                <?php
                if (file_exists($company_signature_path) && !empty(get_option('signature_image'))) {
                ?>
                    <?php if (file_exists($company_signature_path) && !empty(get_option('signature_image'))) { ?>
                        <table style="width: 100%; border: none; margin: 0; padding: 0; border-collapse: collapse;">
                            <tr>
                                <td style="text-align: center; border: none; padding: 0; margin: 0;">
                                    <img src="<?= base_url('uploads/company/' . get_option('signature_image')) ?>" width="140"
                                        height="130" alt="Company Signature">
                                </td>
                            </tr>
                        </table>
                    <?php } else {
                        echo str_repeat("<br>", 10);
                    } ?>
                <?php
                } else {
                    echo "<br><br><br><br><br><br><br><br><br><br>";
                }
                ?>
            </td>
            <td class="signature-cell" style="width: 50%;">
                <strong>The Buyer:</strong><br>
                For and On Behalf of:<br><br>
                <?php
                if (file_exists($customer_sign_path) && !empty($proposal->signature)) {
                    $customer_sign_url = site_url('download/preview_image?path=' . protected_file_url_by_path(get_upload_path_by_type('proposal') . $proposal->id . '/' . $proposal->signature));
                ?>
                    <img src="<?= $customer_sign_url ?>" alt="Company Signature" width="120" height="80">
                <?php
                } else {
                    echo "<br><br><br><br><br><br><br><br><br><br>";
                }
                ?>
            </td>
        </tr>
    </table>

    <!-- NEW PAGE SECTION -->
    <div class="new-page">
        <div>
            <?php echo $proposal->content ?>
        </div>
        <div>
            <table class="signature-section">
                <tr>
                    <td class="signature-cell" style="width: 50%;">
                        <strong>The Seller:</strong><br>
                        For and On Behalf of:<br><br>
                        <?php
                        if (file_exists($company_signature_path) && !empty(get_option('signature_image'))) {
                        ?>
                            <?php if (file_exists($company_signature_path) && !empty(get_option('signature_image'))) { ?>
                                <table style="width: 100%; border: none; margin: 0; padding: 0; border-collapse: collapse;">
                                    <tr>
                                        <td style="text-align: center; border: none; padding: 0; margin: 0;">
                                            <img src="<?= base_url('uploads/company/' . get_option('signature_image')) ?>"
                                                width="140" height="130" alt="Company Signature">
                                        </td>
                                    </tr>
                                </table>
                            <?php } else {
                                echo str_repeat("<br>", 10);
                            } ?>
                        <?php
                        } else {
                            echo "<br><br><br><br><br><br><br><br><br><br>";
                        }
                        ?>
                    </td>
                    <td class="signature-cell" style="width: 50%;">
                        <strong>The Buyer:</strong><br>
                        For and On Behalf of:<br><br>
                        <?php
                        if (file_exists($customer_sign_path) && !empty($proposal->signature)) {
                            $customer_sign_url = site_url('download/preview_image?path=' . protected_file_url_by_path(get_upload_path_by_type('proposal') . $proposal->id . '/' . $proposal->signature));
                        ?>
                            <img src="<?= $customer_sign_url ?>" alt="Company Signature" width="120" height="80">
                        <?php
                        } else {
                            echo "<br><br><br><br><br><br><br><br><br><br>";
                        }
                        ?>
                    </td>
                </tr>
            </table>
        </div>
    </div>


</body>

</html>