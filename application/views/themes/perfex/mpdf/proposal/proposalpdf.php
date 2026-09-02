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
            border: 1px solid #000;
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
            <td colspan="3" class="header-title">Sales Contract Cum Proforma Invoice</td>
        </tr>

        <!-- Row 1: Labels -->
        <tr>
            <td class="info-cell" style="width: 50%; background-color: #e6e6e6;"><i><b>Seller:</b></i></td>
            <td class="info-cell" style="width: 25%; background-color: #e6e6e6;"><i><b>Contract No.:</b></i></td>
            <td class="info-cell" style="width: 25%; background-color: #e6e6e6;"><i><b>Contract Date:</b></i></td>
        </tr>

        <!-- Row 2: Values (Seller spans down) -->
        <tr>
            <td class="info-cell" rowspan="5" style="width: 50%; vertical-align: top;">
                <!-- <b><?php echo get_option('invoice_company_name'); ?></b><br> -->
                <?php
                $seller_info = format_organization_info();
                if (!empty($proposal->proposal_gst_number) && !empty(get_option('company_vat'))) {
                    $seller_info = str_replace(get_option('company_vat'), $proposal->proposal_gst_number, $seller_info);
                } elseif (!empty($proposal->proposal_gst_number) && empty(get_option('company_vat'))) {
                    $seller_info .= '<br/>GST Number: ' . $proposal->proposal_gst_number;
                }
                echo $seller_info;
                ?>
            </td>
            <td class="info-cell" style="vertical-align: top;">
                <?= $proposal->proposal_number_prefix . $proposal->proposal_number ?>
            </td>
            <td class="info-cell" style="vertical-align: top;">
                <?= _d($proposal->date); ?>
            </td>
        </tr>

        <!-- Row 3: Labels for Place -->
        <tr>
            <td class="info-cell" style="background-color: #e6e6e6;"><i><b>Place of Loading:</b></i></td>
            <td class="info-cell" style="background-color: #e6e6e6;"><i><b>Place of Discharge:</b></i></td>
        </tr>

        <!-- Row 4: Values for Place -->
        <tr>
            <td class="info-cell" style="vertical-align: top;"><?= $proposal->loading_place ?></td>
            <td class="info-cell" style="vertical-align: top;"><?= $proposal->discharge_place ?></td>
        </tr>

        <!-- Row 5: Labels for Terms -->
        <tr>
            <td class="info-cell" style="background-color: #e6e6e6;"><i><b>Payment Term:</b></i></td>
            <td class="info-cell" style="background-color: #e6e6e6;"><i><b>Shipment Term:</b></i></td>
        </tr>

        <!-- Row 6: Values for Terms -->
        <tr>
            <td class="info-cell" style="vertical-align: top;"><?= $proposal->payment_term ?></td>
            <td class="info-cell" style="vertical-align: top;"><?= $proposal->shipment_term ?></td>
        </tr>

        <!-- Row 7: Buyer & Notify Party Labels -->
        <tr>
            <td class="info-cell" style="background-color: #e6e6e6;"><i><b>Buyer (Bill To):</b></i></td>
            <td class="info-cell" colspan="2" style="background-color: #e6e6e6;"><i><b>Notify Party (Ship To):</b></i></td>
        </tr>

        <!-- Row 8: Buyer & Notify Party Values -->
        <?php
        $billing_info = '';
        $shipping_info = '';

        if ($proposal->rel_type == 'customer') {
            $CI = &get_instance();
            $CI->load->model('clients_model');
            $client = $CI->clients_model->get($proposal->rel_id);

            if ($client) {
                // Build Billing Info (Buyer)
                if (!empty($client->billing_buyer)) {
                    $billing_info .= '<i><b>' . $client->billing_buyer . '</b></i><br>';
                }

                if (!empty($client->billing_street)) {
                    $billing_info .= nl2br($client->billing_street) . '<br>';
                }

                $billing_country = get_country($client->billing_country);
                $billing_location = [];
                if (!empty($client->billing_city)) $billing_location[] = $client->billing_city;
                if (!empty($client->billing_state)) $billing_location[] = $client->billing_state;
                if ($billing_country) $billing_location[] = $billing_country->short_name;

                if (!empty($billing_location)) {
                    $billing_info .= implode(', ', $billing_location) . '<br>';
                }
                if (!empty($client->billing_mobile_number)) {
                    $billing_info .= 'Mobile: ' . $client->billing_mobile_number . '<br>';
                }
                if (!empty($client->billing_email)) {
                    $billing_info .= 'Email: ' . $client->billing_email . '<br>';
                }
                if (!empty($client->billing_gst_number)) {
                    $billing_info .= 'GST IN: ' . $client->billing_gst_number . '<br>';
                }

                // Build Shipping Info (Notify Party)
                if (!empty($client->shipping_notify_party)) {
                    $shipping_info .= '<i><b>' . $client->shipping_notify_party . '</b></i><br>';
                }
                if (!empty($client->shipping_street)) {
                    $shipping_info .= nl2br($client->shipping_street) . '<br>';
                }

                $shipping_country = get_country($client->shipping_country);
                $shipping_location = [];
                if (!empty($client->shipping_city)) $shipping_location[] = $client->shipping_city;
                if (!empty($client->shipping_state)) $shipping_location[] = $client->shipping_state;
                if ($shipping_country) $shipping_location[] = $shipping_country->short_name;

                if (!empty($shipping_location)) {
                    $shipping_info .= implode(', ', $shipping_location) . '<br>';
                }
                if (!empty($client->shipping_mobile_number)) {
                    $shipping_info .= 'Mobile: ' . $client->shipping_mobile_number . '<br>';
                }
                if (!empty($client->shipping_email)) {
                    $shipping_info .= 'Email: ' . $client->shipping_email . '<br>';
                }
                if (!empty($client->shipping_gst_number)) {
                    $shipping_info .= 'GST IN: ' . $client->shipping_gst_number . '<br>';
                }
            }
        }

        // Fallback for leads or if client is missing
        if (empty($billing_info)) {
            $billing_info = format_proposal_info($proposal, 'pdf');
        }
        ?>
        <tr>
            <td class="info-cell" style="vertical-align: top;">
                <?= $billing_info; ?>
            </td>
            <td class="info-cell" colspan="2" style="vertical-align: top;">
                <?= $shipping_info; ?>
            </td>
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
            <td class="product-header" style="width: 6%;">Sr. No.</td>
            <td class="product-header" style="width: 30%;" colspan="2">Product
            </td>
            <td class="product-header" style="width: 12%;">HSC</td>
            <td class="product-header" style="width: 10%;">Quantity<br>(<?= $qtyunit ?>)</td>
            <td class="product-header" style="width: 17%;">Amount/Unit<br>(In
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

    <!-- Details Grid -->
    <table class="invoice-container" style="margin-top: 2px;">
        <!-- Weight and Shipping Labels -->
        <!-- <tr>
            <td class="info-cell" colspan="2" style="width: 50%; background-color: #e6e6e6;"><i><b>Weight Details:</b></i></td>
            <td class="info-cell" colspan="2" style="width: 50%; background-color: #e6e6e6;"><i><b>Shipping Details:</b></i></td>
        </tr> -->
        <!-- Weight and Shipping Row 1 -->
        <!-- <tr>
            <td class="info-cell" style="width: 25%;">Total Net Weight</td>
            <td class="info-cell" style="width: 25%;"></td>
            <td class="info-cell" style="width: 25%;">Vessel/Transport Name</td>
            <td class="info-cell" style="width: 25%;"></td>
        </tr> -->
        <!-- Weight and Shipping Row 2 -->
        <!-- <tr>
            <td class="info-cell">Total Gross Weight</td>
            <td class="info-cell"></td>
            <td class="info-cell">Container/Truck No.</td>
            <td class="info-cell"></td>
        </tr> -->
        <!-- Weight and Shipping Row 3 -->
        <!-- <tr>
            <td class="info-cell">Total No. of Packages</td>
            <td class="info-cell"></td>
            <td class="info-cell">BL / LR No.</td>
            <td class="info-cell"></td>
        </tr> -->

        <!-- Banking and Registration Labels -->
        <tr>
            <td class="info-cell" colspan="2" style="background-color: #e6e6e6; width: 50%;"><i><b>Banking Details:</b></i></td>
            <td class="info-cell" colspan="2" style="background-color: #e6e6e6; width: 50%;"><i><b>Registration Details:</b></i></td>
        </tr>
        <!-- Banking and Registration Row 1 -->
        <tr>
            <td class="info-cell" style="width: 20%;">Name</td>
            <td class="info-cell" style="width: 30%;"><?= $proposal->bank_ac_name ?></td>
            <td class="info-cell" style="width: 20%;">GSTIN</td>
            <td class="info-cell" style="width: 30%;"><?= !empty($proposal->proposal_gst_number) ? $proposal->proposal_gst_number : get_option('company_vat') ?></td>
        </tr>
        <!-- Row 2 -->
        <tr>
            <td class="info-cell" style="width: 20%;">Account No.</td>
            <td class="info-cell" style="width: 30%;"><?= $proposal->bank_ac_no ?></td>
            <td class="info-cell" style="width: 20%;">CIN</td>
            <td class="info-cell" style="width: 30%;"><?= get_option('company_cin_number') ?? '' ?></td>
        </tr>
        <!-- Row 3 -->
        <tr>
            <td class="info-cell" style="width: 20%;">Bank Name</td>
            <td class="info-cell" style="width: 30%;"><?= $proposal->bank_name ?></td>
            <td class="info-cell" style="width: 20%;">PAN</td>
            <td class="info-cell" style="width: 30%;"><?= get_option('company_pan_number') ?? '' ?></td>
        </tr>
        <!-- Row 4 -->
        <tr>
            <td class="info-cell" style="width: 20%;">IFSC Code</td>
            <td class="info-cell" style="width: 30%;"><?= $proposal->bank_ifsc_code ?></td>
            <td class="info-cell" style="width: 20%;">IEC</td>
            <td class="info-cell" style="width: 30%;"></td>
        </tr>
        <!-- Row 5 -->
        <tr>
            <td class="info-cell" style="width: 20%;">Bank Swift Code</td>
            <td class="info-cell" style="width: 30%;"><?= $proposal->bank_swift_code ?></td>
            <td class="info-cell" style="width: 20%;">TAN</td>
            <td class="info-cell" style="width: 30%;"><?= get_option('company_tan_number') ?? '' ?></td>
        </tr>

        <!-- Notes and Signatory Labels -->
        <tr>
            <td class="info-cell" colspan="4" style="background-color: #e6e6e6;"><i><b>Notes:</b></i></td>
            <!-- <td class="info-cell" colspan="2" style="background-color: #e6e6e6;"><i><b>Authorized Signatory:</b></i></td> -->
        </tr>
        <!-- Notes and Signatory Values -->
        <tr>
            <td class="info-cell" colspan="4" style="vertical-align: top; font-size: 9px; height: 100px;">
                <?= !empty($proposal->notes) ? $proposal->notes . '<br><br>' : '' ?>
                <?= get_option('proposal_pdf_notes') ?>
            </td>
            <!-- <td class="info-cell" colspan="2" style="vertical-align: bottom; text-align: center;">
                <?php
                $company_signature_path = 'uploads/company/' . get_option('signature_image');
                if (file_exists($company_signature_path) && !empty(get_option('signature_image'))) {
                ?>
                    <img src="<?= base_url('uploads/company/' . get_option('signature_image')) ?>" width="140" height="130" alt="Company Signature">
                <?php } else {
                    echo "<br><br><br><br><br><br>";
                } ?>
            </td> -->
        </tr>
    </table>

    <!-- NEW PAGE SECTION -->
    <?php if (get_option('show_pdf_terms_and_conditions') == '1') { ?>
        <div class="new-page">
            <div>
                <?php echo $proposal->content ?>
            </div>
            <div>

            </div>
        </div>
    <?php } ?>
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


</body>

</html>