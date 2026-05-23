<!DOCTYPE html>
<html>
<?php
$currencyData = get_currency($invoice->currency);
$getTax = get_tax_by_relation($invoice->id, "invoice");
$applied_credits = 0;
$applied_credits_data = $this->credit_notes_model->get_applied_invoice_credits($invoice->id);
if (!empty($applied_credits_data)) {
    foreach ($applied_credits_data as $key => $item) {
        $applied_credits += $item['amount'];
    }
}
$invoice_total = $invoice->total - $applied_credits;
?>

<head>
    <meta charset="UTF-8">
    <title><?= ($invoice->pdf_type == "tax-invoice") ? "Tax Invoice" : "Packing / Weight List" ?></title>
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
    <table class="invoice-container">
        <!-- Header -->
        <tr>
            <td colspan="4" class="header-title">
                <?= ($invoice->pdf_type == "tax-invoice") ? "Tax Invoice" : "Packing / Weight List" ?>
            </td>
        </tr>
        <tr>
            <td class="info-cell label" colspan="2">Invoice No. : <?= format_invoice_number($invoice->id); ?></td>
            <td class="info-cell label" colspan="2">Invoice Date : <?= _d($invoice->date); ?></td>
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
                <?= format_customer_info($invoice, 'invoice', 'billing'); ?>
            </td>
        </tr>

        <tr class="info-row">
            <td class="info-cell label " width="25%">Place of Loading:</td>
            <td class="info-cell label " width="25%">Place of Discharge:</td>
            <td class="info-cell label " width="25%">Payment Term:</td>
            <td class="info-cell label " width="25%">Shipment Term:</td>
        </tr>
        <tr class="info-row">
            <td class="info-cell"><?= $invoice->loading_place ?></td>
            <td class="info-cell"><?= $invoice->discharge_place ?></td>
            <td class="info-cell"><?= $invoice->payment_term ?></td>
            <td class="info-cell"><?= $invoice->shipment_term ?></td>
        </tr>
    </table>

    <!-- Product Details -->
    <?php
    $qtyunit = "";
    if (isset($invoice->items[0]['unit'])) {
        $qtyunit = $invoice->items[0]['unit'];
    }
    ?>
    <table class="product-table">
        <tr>
            <td class="product-header" style="width: 6%;"><span class="">Sr. No.</span></td>
            <?php if ($invoice->pdf_type == "packing-list") { ?>
                <td class="product-header" style="width: 10%;"><span class="">Kind of Packages</span></td>
                <td class="product-header" style="width: 30%;" colspan="2"><span class="">Product Name & Description</span>
                </td>
                <td class="product-header" style="width: 12%;"><span class="">HS Code</span></td>
                <td class="product-header" style="width: 10%;"><span class="">Quantity<br>(<?= $qtyunit ?>)</span></td>
                <td class="product-header" style="width: 12%;"><span class="">Net Weight (in Kgs)</span></td>
                <td class="product-header" style="width: 12%;"><span class="">Gross Weight (in Kgs)</span></td>
            <?php } else { ?>
                <td class="product-header" style="width: 32%;" colspan="2"><span class="">Product Name & Description</span>
                </td>
                <td class="product-header" style="width: 12%;"><span class="">HS Code</span></td>
                <td class="product-header" style="width: 10%;"><span class="">Quantity<br>(<?= $qtyunit ?>)</span></td>
                <td class="product-header" style="width: 17%;"><span class="">Amount/Unit<br>(In
                        <?= $currencyData->name ?>)</span></td>
                <td class="product-header" style="width: 17%;"><span class="">Total Amount<br>(In
                        <?= $currencyData->name ?>)</span></td>
            <?php } ?>
        </tr>
        <?php
        $totalQty = 0;
        $no = 0;
        foreach ($invoice->items as $key => $item) {
            $total = $item['rate'] * $item['qty'];
            $totalQty += $item['qty'];
            $net_weight = $item['net_weight'] * $item['qty'];
            $gross_weight = $item['gross_weight'] * $item['qty'];
        ?>
            <tr>
                <td class="product-cell"><?= $no += 1; ?></td>
                <?php if ($invoice->pdf_type == "packing-list") { ?>
                    <td class="product-cell"><?= $item['kind_of_packages']; ?></td>
                <?php } ?>
                <td class="product-cell left" colspan="2">
                    <?= $item['description']; ?><br>
                    <small><?= $item['long_description']; ?></small>
                </td>
                <td class="product-cell"><?= $item['hsn_code']; ?></td>
                <td class="product-cell"><?= (int) $item['qty']; ?></td>
                <?php if ($invoice->pdf_type == "packing-list") { ?>
                    <td class="product-cell"><?= $net_weight; ?></td>
                    <td class="product-cell"><?= $gross_weight; ?></td>
                <?php } else { ?>
                    <td class="product-cell"><?= number_format($item['rate'], 2, '.', ''); ?></td>
                    <td class="product-cell"><?= number_format($total, 2, '.', ''); ?></td>
                <?php } ?>
            </tr>
        <?php } ?>

        <?php if ($invoice->pdf_type == "packing-list") { ?>
            <tr>
                <td class="product-cell"></td>
                <td class="product-cell label"><?= $invoice->total_packages; ?></td>
                <td class="product-cell label " colspan="3" style="text-align: right;">Total</td>
                <td class="product-cell label"><?= (int) $totalQty; ?></td>
                <td class="product-cell label"><?= $invoice->total_net_weight; ?></td>
                <td class="product-cell label"><?= $invoice->total_gross_weight; ?></td>
            </tr>
        <?php } else { ?>
            <tr class="amount-row">
                <td class="product-cell" colspan="6" style="text-align: right; font-weight: bold;">Sub Total Amount</td>
                <td class="product-cell" style="font-weight: bold;"><?= number_format($invoice->subtotal, 2, '.', ''); ?>
                </td>
            </tr>
            <?php
            if (is_sale_discount_applied($invoice) && $invoice->discount_type == 'before_tax') {
            ?>
                <tr class="amount-row">
                    <td class="product-cell" colspan="6" style="text-align: right; font-weight: bold;">
                        <?php echo _l('estimate_discount');
                        if (is_sale_discount($invoice, 'percent')) {
                            echo ' (' . app_format_number($invoice->discount_percent, true) . '%)';
                        }
                        ?>
                    </td>
                    <td class="product-cell" style="font-weight: bold;"> -
                        <?= number_format($invoice->discount_total, 2, '.', ''); ?>
                    </td>
                </tr>
            <?php
            }
            ?>
            <?php
            $dynamicAmounts = get_dynamic_amount_fields("invoice", $invoice->id);
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
            <?php if ($invoice->type == "0") { ?>
                <?php if (!empty($getTax)) { ?>
                    <?php if ($getTax->taxrate != 0) { ?>
                        <tr class="amount-row">
                            <td class="product-cell" colspan="6" style="text-align: right;"><strong>Taxable Amount</strong></td>
                            <td class="product-cell"><?= number_format($invoice->taxable_amount, 2, '.', ''); ?></td>
                        </tr>
                        <tr class="amount-row">
                            <td class="product-cell" colspan="6" style="text-align: right;"><?= $getTax->taxname ?>
                                (<?= $getTax->taxrate ?>%)</td>
                            <td class="product-cell"><?= number_format($invoice->total_tax, 2, '.', ''); ?></td>
                        </tr>
                    <?php } ?>
                <?php } ?>
            <?php } ?>
            <?php
            if (is_sale_discount_applied($invoice) && $invoice->discount_type == 'after_tax') {
            ?>
                <tr class="amount-row">
                    <td class="product-cell" colspan="6" style="text-align: right; font-weight: bold;">
                        <?php echo _l('estimate_discount');
                        if (is_sale_discount($invoice, 'percent')) {
                            echo ' (' . app_format_number($invoice->discount_percent, true) . '%)';
                        }
                        ?>
                    </td>
                    <td class="product-cell" style="font-weight: bold;"> -
                        <?= number_format($invoice->discount_total, 2, '.', ''); ?>
                    </td>
                </tr>
            <?php
            }
            ?>
            <?php if ($invoice->adjustment > 0) { ?>
                <tr class="amount-row">
                    <td class="product-cell" colspan="6" style="text-align: right;"><?= _l('estimate_adjustment') ?></td>
                    <td class="product-cell"><?= number_format($invoice->adjustment, 2, '.', ''); ?></td>
                </tr>
            <?php } ?>
            <?php if ($applied_credits > 0) { ?>
                <tr class="amount-row">
                    <td class="product-cell" colspan="6" style="text-align: right; font-weight: bold;">Applied Credits</td>
                    <td class="product-cell" style="font-weight: bold;"> - <?= number_format($applied_credits, 2, '.', ''); ?>
                    </td>
                </tr>
            <?php } ?>
            <tr class="total-row">
                <td class="product-cell" colspan="4" style="text-align: left; font-weight: bold;">
                    <span class="">Amount In Word:</span>
                    <?= strtoupper(convertNumberToWords($invoice_total, $invoice->currency)) ?>
                </td>
                <td class="product-cell" style="font-weight: bold; font-size: 12px;"><?= $totalQty; ?></td>
                <td class="product-cell" style="font-weight: bold; font-size: 12px;">Total Amount</td>
                <td class="product-cell" style="text-align: center; font-weight: bold; font-size: 12px;">
                    <?= number_format($invoice_total, 2, '.', ''); ?>
                </td>
            </tr>
        <?php } ?>
    </table>

    <!-- Shipping and Weight Details -->
    <table class="bank-details">
        <tr>
            <td class="bank-cell label " colspan="2" style="width: 12%;">Shipping Details:</td>
            <td class="bank-cell label" colspan="2" style="width: 12%;">Weight Details:</td>
        </tr>
        <tr>
            <td class="bank-cell" width="25%">Transporter:</td>
            <td class="bank-cell" width="25%"><?= $invoice->transporter ?></td>
            <td class="bank-cell" width="25%">Total Net Weight:</td>
            <td class="bank-cell" width="25%"><?= $invoice->total_net_weight ?></td>
        </tr>
        <tr>
            <td class="bank-cell">LR No. / BL No.:</td>
            <td class="bank-cell"><?= $invoice->lr_br_no ?></td>
            <td class="bank-cell">Total Gross Weight:</td>
            <td class="bank-cell"><?= $invoice->total_gross_weight ?></td>
        </tr>
        <tr>
            <td class="bank-cell">Vehicle No.:</td>
            <td class="bank-cell"><?= $invoice->vehicle_no ?></td>
            <td class="bank-cell">Total No. of Packages:</td>
            <td class="bank-cell"><?= $invoice->total_packages ?></td>
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
            <td class="bank-cell" width="25%"><?= $invoice->bank_ac_name ?></td>
            <td class="bank-cell" width="25%">GST Number:</td>
            <td class="bank-cell" width="25%"><?= get_option('company_vat') ?></td>
        </tr>
        <tr>
            <td class="bank-cell">Account No.:</td>
            <td class="bank-cell"><?= $invoice->bank_ac_no ?></td>
            <td class="bank-cell">PAN/IEC Number:</td>
            <td class="bank-cell"><?= get_option('company_pan_number') ?></td>
        </tr>
        <tr>
            <td class="bank-cell">Bank Name:</td>
            <td class="bank-cell"><?= $invoice->bank_name ?></td>
            <td class="bank-cell">TAN Number:</td>
            <td class="bank-cell"><?= get_option('company_tan_number') ?></td>
        </tr>
        <tr>
            <td class="bank-cell">IFSC Code:</td>
            <td class="bank-cell"><?= $invoice->bank_ifsc_code ?></td>
            <td class="bank-cell" colspan="2" rowspan="3"><strong>Notes:</strong> <?= $invoice->clientnote ?></td>
        </tr>
        <tr>
            <td class="bank-cell">Swift Code:</td>
            <td class="bank-cell"><?= $invoice->bank_swift_code ?></td>
        </tr>
        <tr>
            <td class="bank-cell">Address:</td>
            <td class="bank-cell"><?= $invoice->bank_address ?></td>
        </tr>
    </table>

    <!-- Signature Section -->
    <?php
    $company_signature_path = 'uploads/company/' . get_option('signature_image');
    ?>
    <table class="signature-section">
        <tr>
            <td class="signature-cell" style="height: 100%;">
                <?= $invoice->terms ?>
            </td>
        </tr>
        <?php if (!empty($invoice->clientnote)) { ?>
            <tr>
                <td class="signature-cell" style="height: 100%;">
                    <strong>Notes :</strong>
                    <br/> <?= $invoice->clientnote ?>
                </td>
            </tr>
        <?php } ?>
        <tr>
            <td class="signature-cell" style="text-align:right;  vertical-align: bottom;">
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
                (Authorised Signatory)
            </td>
        </tr>
    </table>
</body>

</html>