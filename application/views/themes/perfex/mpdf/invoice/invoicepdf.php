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
if ($invoice->pdf_type == "custom-invoice" || $invoice->pdf_type == "commercial-invoice") {
    $invoice_total -= $invoice->total_tax;
}

$original_currency_name = isset($currencyData) ? $currencyData->name : '';
$exchange_rate = 1;
if ($invoice->pdf_type == "tax-invoice" && isset($currencyData) && strtoupper($currencyData->name) != 'INR' && !empty($invoice->exchange_rate)) {
    $exchange_rate = (float)$invoice->exchange_rate;

    $currencyData->name = 'INR';
    $invoice->currency = 'INR';

    $invoice->subtotal = $invoice->subtotal * $exchange_rate;
    $invoice->discount_total = $invoice->discount_total * $exchange_rate;
    $invoice->taxable_amount = $invoice->taxable_amount * $exchange_rate;
    $invoice->total_tax = $invoice->total_tax * $exchange_rate;
    $invoice->adjustment = $invoice->adjustment * $exchange_rate;
    $applied_credits = $applied_credits * $exchange_rate;
    $invoice_total = $invoice_total * $exchange_rate;

    if (!empty($invoice->items)) {
        foreach ($invoice->items as $k => $item) {
            $invoice->items[$k]['rate'] = $item['rate'] * $exchange_rate;
        }
    }
}
?>

<head>
    <meta charset="UTF-8">
    <title>
        <?php
        if ($invoice->pdf_type == "tax-invoice") {
            echo "GST Invoice";
        } elseif ($invoice->pdf_type == "custom-invoice") {
            echo "Custom Invoice";
        } elseif ($invoice->pdf_type == "commercial-invoice") {
            echo "Commercial Invoice";
        } else {
            echo "Packing / Weight List";
        }
        ?>
    </title>
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
                <?php
                if ($invoice->pdf_type == "tax-invoice") {
                    echo "GST Invoice";
                } elseif ($invoice->pdf_type == "custom-invoice") {
                    echo "Custom Invoice";
                } elseif ($invoice->pdf_type == "commercial-invoice") {
                    echo "Commercial Invoice";
                } else {
                    echo "Packing / Weight List";
                }
                ?>
            </td>
        </tr>
        <?php
        $country_name = '';
        if ($invoice->shipping_country) {
            $country_name = get_country($invoice->shipping_country)->short_name;
        } elseif (isset($invoice->client->country) && $invoice->client->country != 0) {
            $country_name = get_country($invoice->client->country)->short_name;
        }

        if (($invoice->pdf_type == "tax-invoice" || $invoice->pdf_type == "custom-invoice") && strtolower($country_name) != 'india') {
        ?>
            <tr>
                <td colspan="4" class="info-cell" style="text-align: center;">
                    Supply of Goods for Export under payment of Integrated Tax (IGST)
                </td>
            </tr>
        <?php } ?>

        <!-- Seller and Invoice Info -->
        <tr>
            <td class="info-cell label" colspan="2" style="width: 50%;">Seller:</td>
            <td class="info-cell label" style="width: 25%;">Invoice No.:</td>
            <td class="info-cell label" style="width: 25%;">Invoice Date:</td>
        </tr>
        <tr>
            <td class="info-cell" colspan="2" rowspan="5">
                <?php
                // Show the invoice-specific GST number in the Seller block
                // format_organization_info() always inserts company_vat; override it here
                $seller_info = format_organization_info();
                if (!empty($invoice->gst_number) && !empty(get_option('company_vat'))) {
                    // Swap the global GST with the invoice-specific one in the rendered string
                    $seller_info = str_replace(get_option('company_vat'), $invoice->gst_number, $seller_info);
                } elseif (!empty($invoice->gst_number) && empty(get_option('company_vat'))) {
                    // company_vat was empty so {vat_number_with_label} was stripped; append GST manually
                    $seller_info .= '<br/>GST Number: ' . $invoice->gst_number;
                }
                echo $seller_info;
                ?>
            </td>
            <td class="info-cell" style="vertical-align: middle;"><?= format_invoice_number($invoice->id); ?></td>
            <td class="info-cell" style="   vertical-align: middle;"><?= _d($invoice->date); ?></td>
        </tr>
        <tr>
            <td class="info-cell label">Place of Loading:</td>
            <td class="info-cell label">Place of Discharge:</td>
        </tr>
        <tr>
            <td class="info-cell"><?= $invoice->loading_place ?></td>
            <td class="info-cell"><?= $invoice->discharge_place ?></td>
        </tr>
        <tr>
            <td class="info-cell label">Payment Term:</td>
            <td class="info-cell label">Shipment Term:</td>
        </tr>
        <tr>
            <td class="info-cell"><?= $invoice->payment_term ?></td>
            <td class="info-cell"><?= $invoice->shipment_term ?></td>
        </tr>

        <!-- Buyer and Notify Party -->
        <?php
        $billing_info  = get_client_address_info($invoice->client, 'billing');
        $shipping_info = get_client_address_info($invoice->client, 'shipping');
        ?>
        <tr class="info-row">
            <td class="info-cell label" colspan="2" style="width: 50%;">Buyer (Bill To):</td>
            <td class="info-cell label" colspan="2" style="width: 50%;">Notify Party (Ship To):</td>
        </tr>
        <tr class="info-row">
            <td class="info-cell" colspan="2">
                <strong><?= $billing_info['name'] ?></strong><br>
                <?= $billing_info['address'] ?>
            </td>
            <td class="info-cell" colspan="2">
                <strong><?= $shipping_info['name'] ?></strong><br>
                <?= $shipping_info['address'] ?>
            </td>
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
                <td class="product-header" style="width: 30%;" colspan="2"><span class="">Product</span></td>
                <td class="product-header" style="width: 10%;"><span class="">Kind of Packages</span></td>
                <td class="product-header" style="width: 12%;"><span class="">HSN</span></td>
                <td class="product-header" style="width: 10%;"><span class="">Quantity<br>(<?= $qtyunit ?>)</span></td>
                <td class="product-header" style="width: 12%;"><span class="">Net Weight <br>(in Kgs)</span></td>
                <td class="product-header" style="width: 12%;"><span class="">Gross Weight <br>(in Kgs)</span></td>
            <?php } else { ?>
                <td class="product-header" style="width: 28%;" colspan="2"><span class="">Product</span></td>
                <td class="product-header" style="width: 12%;"><span class="">HSN</span></td>
                <td class="product-header" style="width: 10%;"><span class="">Quantity<br>(<?= $qtyunit ?>)</span></td>
                <td class="product-header" style="width: 17%;"><span class="">Amount/Unit<br>(In <?= $currencyData->name ?>)</span></td>
                <td class="product-header" style="width: 17%;"><span class="">Total Amount<br>(In <?= $currencyData->name ?>)</span></td>
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
                <td class="product-cell left" colspan="2">
                    <?= $item['description']; ?><br>
                    <small><?= $item['long_description']; ?></small>
                </td>
                <?php if ($invoice->pdf_type == "packing-list") { ?>
                    <td class="product-cell"><?= $item['kind_of_packages']; ?></td>
                <?php } ?>
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
                <td class="product-cell label" colspan="2"></td>
                <td class="product-cell label"><?= $invoice->total_packages; ?></td>
                <td class="product-cell label" style="text-align: right;">Total</td>
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
                        <td class="product-cell"><?= number_format($item['amount'] * $exchange_rate, 2, '.', ''); ?></td>
                    </tr>
                <?php
                }
                ?>
            <?php } ?>
            <?php if ($invoice->pdf_type != "custom-invoice" && $invoice->pdf_type != "commercial-invoice") { ?>
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
                    <?= ucwords(convertNumberToWords($invoice_total, $invoice->currency)) ?>
                </td>
                <td class="product-cell" style="font-weight: bold; font-size: 12px;"><?= $totalQty; ?></td>
                <td class="product-cell" style="font-weight: bold; font-size: 12px;">Total Amount</td>
                <td class="product-cell" style="text-align: center; font-weight: bold; font-size: 12px;">
                    <?= number_format($invoice_total, 2, '.', ''); ?>
                </td>
            </tr>
        <?php } ?>
    </table>

    <!-- Combined Details and Signature Section -->
    <table class="bank-details">
        <tr>
            <td class="bank-cell label" colspan="2" style="width: 50%;"><em>Weight Details:</em></td>
            <td class="bank-cell label" colspan="2" style="width: 50%;"><em>Shipping Details:</em></td>
        </tr>
        <tr>
            <td class="bank-cell" width="19%">Total Net Weight</td>
            <td class="bank-cell" width="31%"><?= convert_to_metric_tons($invoice->total_net_weight) ?> MT</td>
            <td class="bank-cell" width="19%">Vessel/Transporter</td>
            <td class="bank-cell" width="31%"><?= $invoice->transporter ?></td>
        </tr>
        <tr>
            <td class="bank-cell" width="19%">Total Gross Weight</td>
            <td class="bank-cell" width="31%"><?= convert_to_metric_tons($invoice->total_gross_weight) ?> MT</td>
            <td class="bank-cell" width="19%">Container/Truck No.</td>
            <td class="bank-cell" width="31%"><?= $invoice->vehicle_no ?></td>
        </tr>
        <tr>
            <td class="bank-cell" width="19%">Total No. of Packages</td>
            <td class="bank-cell" width="31%"><?= $invoice->total_packages ?></td>
            <td class="bank-cell" width="19%">BL / LR No.</td>
            <td class="bank-cell" width="31%"><?= $invoice->lr_br_no ?></td>
        </tr>
        <tr>
            <td class="bank-cell label" colspan="2" style="width: 50%;"><em>Banking Details:</em></td>
            <td class="bank-cell label" colspan="2" style="width: 50%;"><em>Registration Details:</em></td>
        </tr>
        <tr>
            <td class="bank-cell" style="width: 19%;">Name</td>
            <td class="bank-cell" style="width: 31%;"><?= $invoice->bank_ac_name ?></td>
            <td class="bank-cell" style="width: 19%;">GSTIN</td>
            <td class="bank-cell" style="width: 31%;"><?= !empty($invoice->gst_number) ? $invoice->gst_number : get_option('company_vat') ?></td>
        </tr>
        <tr>
            <td class="bank-cell" style="width: 19%;">Account No.</td>
            <td class="bank-cell" style="width: 31%;"><?= $invoice->bank_ac_no ?></td>
            <td class="bank-cell" style="width: 19%;">CIN</td>
            <td class="bank-cell" style="width: 31%;"><?= get_option('company_cin_number') ?></td>
        </tr>
        <tr>
            <td class="bank-cell" style="width: 19%;">Bank Name</td>
            <td class="bank-cell" style="width: 31%;"><?= $invoice->bank_name ?></td>
            <td class="bank-cell" style="width: 19%;">PAN</td>
            <td class="bank-cell" style="width: 31%;"><?= get_option('company_pan_number') ?></td>
        </tr>
        <tr>
            <td class="bank-cell" style="width: 19%;">IFSC Code</td>
            <td class="bank-cell" style="width: 31%;"><?= $invoice->bank_ifsc_code ?></td>
            <td class="bank-cell" style="width: 19%;">IEC</td>
            <td class="bank-cell" style="width: 31%;"><?= get_option('company_iec_number') ?></td>
        </tr>
        <tr>
            <td class="bank-cell" style="width: 19%;">Swift Code</td>
            <td class="bank-cell" style="width: 31%;"><?= $invoice->bank_swift_code ?></td>
            <td class="bank-cell" style="width: 19%;">TAN</td>
            <td class="bank-cell" style="width: 31%;"><?= get_option('company_tan_number') ?></td>
        </tr>
        <tr>
            <td class="bank-cell label" colspan="2"><em>Notes:</em></td>
            <td class="bank-cell label" colspan="2"><em>Authorized Signatory:</em></td>
        </tr>
        <tr>
            <td class="bank-cell" colspan="2" style="vertical-align: top; font-size: 9px;">
                <?php
                $setting_notes = '';
                if ($invoice->pdf_type == "tax-invoice") {
                    $setting_notes = get_option('notes_gst_invoice');
                } elseif ($invoice->pdf_type == "custom-invoice") {
                    $setting_notes = get_option('notes_custom_invoice');
                } elseif ($invoice->pdf_type == "commercial-invoice") {
                    $setting_notes = get_option('notes_commercial_invoice');
                } else {
                    $setting_notes = get_option('notes_packing_weight_list');
                }

                if (!empty($setting_notes)) {
                    echo $setting_notes . '<br><br>';
                }

                if (!empty($invoice->clientnote)) {
                    echo $invoice->clientnote . '<br><br>';
                }

                if (isset($original_currency_name) && strtoupper($original_currency_name) != 'INR' && $invoice->pdf_type == "tax-invoice") {
                    if (!empty($invoice->notification_number)) {
                        echo '<strong>Notification Number:</strong> ' . $invoice->notification_number . '<br>';
                    }
                    if (!empty($invoice->exchange_rate)) {
                        echo '<strong>Exchange Rate:</strong> ' . $invoice->exchange_rate . '<br>';
                    }
                    if (!empty($invoice->notification_number) || !empty($invoice->exchange_rate)) {
                        echo '<br>';
                    }
                }
                ?>
                <?= $invoice->terms ?>
            </td>
            <td class="bank-cell" colspan="2" style="text-align: right; vertical-align: bottom; height: 120px;">
                <?php
                $company_signature_path = 'uploads/company/' . get_option('signature_image');
                if (file_exists($company_signature_path) && !empty(get_option('signature_image'))) {
                ?>
                    <div style="text-align: right">
                        <img src="<?= base_url('uploads/company/' . get_option('signature_image')) ?>"
                            alt="Company Signature" width="140" height="130">
                    </div>
                <?php
                }
                ?>
            </td>
        </tr>
    </table>
</body>

</html>