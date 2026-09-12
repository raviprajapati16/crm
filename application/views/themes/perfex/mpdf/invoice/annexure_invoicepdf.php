    <?php

    defined('BASEPATH') or exit('No direct script access allowed');

    // Fetch Annexure settings
    $shipping_bill_no = get_option('annexure_shipping_bill_no');
    $shipping_bill_date = get_option('annexure_shipping_bill_date');
    $range_of_customs = get_option('annexure_range_of_customs');
    $commissionerate_of_customs = get_option('annexure_commissionerate_of_customs');
    $division_of_customs = get_option('annexure_division_of_customs');

    $exporter_name = get_option('annexure_exporter_name');
    $exporter_address = get_option('annexure_exporter_address');
    $iec_number = get_option('annexure_iec_number');
    $gstin = get_option('annexure_gstin');
    $pan_number = get_option('annexure_pan_number');
    $cin_number = get_option('annexure_cin_number');
    $aeo_certificate_number = get_option('annexure_aeo_certificate_number');
    $permission_order_no = get_option('annexure_permission_order_no');
    $stuffing_address = get_option('annexure_stuffing_address');
    $authorised_signatory_name = get_option('annexure_authorised_signatory_name');
    $authorised_signatory_designation = get_option('annexure_authorised_signatory_designation');
    $examination_agency_name = get_option('annexure_examination_agency_name');

    $declaration = get_option('annexure_declaration');

    // Fetch Invoice details
    $tax_invoice_no = format_invoice_number($invoice->id);
    $tax_invoice_date = _d($invoice->date);

    // Buyer details (billing)
    $billing_info      = get_client_address_info($invoice->client, 'billing');
    $buyer_name        = $billing_info['name'];
    $buyer_address     = $billing_info['address'];

    // Notify Party (shipping)
    $shipping_info         = get_client_address_info($invoice->client, 'shipping');
    $notify_party_name     = $shipping_info['name'];
    $notify_party_address  = $shipping_info['address'];

    // Destination
    $destination = !empty($invoice->client->shipping_country) && $invoice->client->shipping_country != 0
        ? get_country($invoice->client->shipping_country)->short_name
        : (isset($invoice->client->country) && $invoice->client->country != 0 ? get_country($invoice->client->country)->short_name : '');

    // Containers
    $containers = isset($invoice->containers) ? $invoice->containers : [];

    ?>
    <style>
        table.annexure-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            font-size: 9pt;
            font-family: Arial, Helvetica, sans-serif;
        }

        table.annexure-table th,
        table.annexure-table td {
            border: 1px solid #000;
            padding: 3px 4px;
            vertical-align: middle;
        }

        .header-title {
            text-align: center;
            font-weight: bold;
            font-size: 10pt;
            line-height: 1.25;
            padding: 2px 0 !important;
        }

        .section-title {
            font-weight: bold;
            background-color: #f2f2f2;
        }

        .vertical-text {
            text-align: center;
            font-weight: bold;
            font-size: 10pt;
            white-space: nowrap;
            /* For HTML Preview in browser */
            writing-mode: vertical-rl;
            transform: rotate(180deg);
        }

        .label-col {
            width: 33%;
        }

        .value-col {
            width: 62%;
        }

        .section-col {
            width: 5%;
        }

        .declaration-box {
            height: 125px;
            vertical-align: top !important;
            padding: 8px !important;
        }

        .signature-box {
            height: 80px;
            text-align: right;
            vertical-align: bottom;
            padding-bottom: 5px;
            padding-right: 10px;
        }

        .signature-box img {
            max-width: 140px;
            max-height: 80px;
            object-fit: contain;
        }

        .field-label {
            /* color: #004d99; */
            /* Bluish color as in screenshot for Custom/Exporter Details */
        }

        .invoice-label {
            /* color: #cc5200; */
            /* Orangeish color as in screenshot for Invoice Details */
        }

        .signature-box {
            height: 100px;
            text-align: right;
            vertical-align: bottom;
            padding-bottom: 5px;
            padding-right: 10px;
        }

        .container-th {
            /* color: #cc0000; */
            font-weight: bold;
            font-size: 8pt;
            text-align: center;
        }

        .container-td {
            text-align: center;
            font-size: 8pt;
        }
    </style>

    <table class="annexure-table">
        <colgroup>
            <col style="width: 5%;">
            <col style="width: 33%;">
            <col style="width: 62%;">
        </colgroup>
        <tr>
            <td colspan="3" class="header-title">ANNEXURE</td>
        </tr>
        <tr>
            <td colspan="3" class="header-title">EXAMINATION REPORT FOR FACTORY SEALED PACKAGES/CONTAINER</td>
        </tr>

        <!-- Custom Details -->
        <tr>
            <td rowspan="5" class="vertical-text section-col" text-rotate="90">Custom Details</td>
            <td class="field-label label-col">Shipping Bill No.</td>
            <td class="value-col"><?php echo htmlspecialchars($shipping_bill_no); ?></td>
        </tr>
        <tr>
            <td class="field-label">Shipping Bill Date</td>
            <td><?php echo $shipping_bill_date ? _d($shipping_bill_date) : ''; ?></td>
        </tr>
        <tr>
            <td class="field-label">Range of Customs</td>
            <td><?php echo htmlspecialchars($range_of_customs); ?></td>
        </tr>
        <tr>
            <td class="field-label">Commissionerate of Customs</td>
            <td><?php echo htmlspecialchars($commissionerate_of_customs); ?></td>
        </tr>
        <tr>
            <td class="field-label">Division of Customs</td>
            <td><?php echo htmlspecialchars($division_of_customs); ?></td>
        </tr>

        <!-- Exporter & Permission Details -->
        <tr>
            <td rowspan="12" class="vertical-text" text-rotate="90">Exporter & Permission Details</td>
            <td class="field-label">Name of Exporter</td>
            <td><?php echo htmlspecialchars($exporter_name); ?></td>
        </tr>
        <tr>
            <td class="field-label">Address of Exporter</td>
            <td><?php echo htmlspecialchars($exporter_address); ?></td>
        </tr>
        <tr>
            <td class="field-label">IEC Number</td>
            <td><?php echo htmlspecialchars($iec_number); ?></td>
        </tr>
        <tr>
            <td class="field-label">GSTIN</td>
            <td><?php echo htmlspecialchars($gstin); ?></td>
        </tr>
        <tr>
            <td class="field-label">PAN Number</td>
            <td><?php echo htmlspecialchars($pan_number); ?></td>
        </tr>
        <tr>
            <td class="field-label">CIN Number</td>
            <td><?php echo htmlspecialchars($cin_number); ?></td>
        </tr>
        <tr>
            <td class="field-label">AEO Certificate Number</td>
            <td><?php echo htmlspecialchars($aeo_certificate_number); ?></td>
        </tr>
        <tr>
            <td class="field-label">Permission Order No.</td>
            <td><?php echo htmlspecialchars($permission_order_no); ?></td>
        </tr>
        <tr>
            <td class="field-label">Stuffing Address</td>
            <td><?php echo htmlspecialchars($stuffing_address); ?></td>
        </tr>
        <tr>
            <td class="field-label">Name of Authorised Signatory</td>
            <td><?php echo htmlspecialchars($authorised_signatory_name); ?></td>
        </tr>
        <tr>
            <td class="field-label">Designation of Authorised Signatory</td>
            <td><?php echo htmlspecialchars($authorised_signatory_designation); ?></td>
        </tr>
        <tr>
            <td class="field-label">Examination Agency/Officer Name</td>
            <td><?php echo htmlspecialchars($examination_agency_name); ?></td>
        </tr>

        <!-- Cargo & Export Shipping Details -->
        <tr>
            <td rowspan="6" class="vertical-text" text-rotate="90">Cargo & Export Shipping Details</td>
            <td class="invoice-label">Tax Invoice No.</td>
            <td><?php echo htmlspecialchars($tax_invoice_no); ?></td>
        </tr>
        <tr>
            <td class="invoice-label">Tax Invoice Date</td>
            <td><?php echo htmlspecialchars($tax_invoice_date); ?></td>
        </tr>
        <tr>
            <td class="invoice-label">Name and address of buyer</td>
            <td>
                <strong><?php echo htmlspecialchars($buyer_name); ?></strong><br>
                <?php echo strip_tags($buyer_address, '<br>'); ?>
            </td>
        </tr>
        <tr>
            <td class="invoice-label">Name and Address of Notify party</td>
            <td>
                <strong><?php echo htmlspecialchars($notify_party_name); ?></strong><br>
                <?php echo strip_tags($notify_party_address, '<br>'); ?>
            </td>
        </tr>
        <tr>
            <td class="invoice-label">Country of Final Destination</td>
            <td><?php echo htmlspecialchars($destination); ?></td>
        </tr>
        <tr>
            <td colspan="2" style="padding: 0;">
                <table style="width: 100%; border-collapse: collapse; margin: 0; border: none;">
                    <tr>
                        <th class="container-th" style="border-top: none; border-left: none; border-right: 1px solid #000; border-bottom: 1px solid #000;">Container No.</th>
                        <th class="container-th" style="border-top: none; border-left: none; border-right: 1px solid #000; border-bottom: 1px solid #000;">Stuffing Date</th>
                        <th class="container-th" style="border-top: none; border-left: none; border-right: 1px solid #000; border-bottom: 1px solid #000;">Size</th>
                        <th class="container-th" style="border-top: none; border-left: none; border-right: 1px solid #000; border-bottom: 1px solid #000;">Shipping Line<br>Seal No.</th>
                        <th class="container-th" style="border-top: none; border-left: none; border-right: 1px solid #000; border-bottom: 1px solid #000;">RFID Seal No.</th>
                        <th class="container-th" style="border-top: none; border-left: none; border-right: 1px solid #000; border-bottom: 1px solid #000;">Total<br>Packages</th>
                        <th class="container-th" style="border-top: none; border-left: none; border-right: 1px solid #000; border-bottom: 1px solid #000;">Net Weight<br>(in Kgs)</th>
                        <th class="container-th" style="border-top: none; border-left: none; border-right: none; border-bottom: 1px solid #000;">Gross Weight<br>(in Kgs)</th>
                    </tr>
                    <?php
                    $container_rows = count($containers);
                    for ($i = 0; $i < $container_rows; $i++) {
                        $c = isset($containers[$i]) ? $containers[$i] : [];
                        // Last row has no bottom border inside the table structure
                        $b_bottom = ($i == $container_rows - 1) ? 'border-bottom: none;' : 'border-bottom: 1px solid #000;';
                    ?>
                        <tr>
                            <td class="container-td" style="height: 20px; border-top: none; border-left: none; border-right: 1px solid #000; <?php echo $b_bottom; ?>"><?php echo isset($c['container_no']) ? htmlspecialchars($c['container_no']) : '&nbsp;'; ?></td>
                            <td class="container-td" style="height: 20px; border-top: none; border-left: none; border-right: 1px solid #000; <?php echo $b_bottom; ?>"><?php echo (isset($c['stuffing_date']) && !empty($c['stuffing_date'])) ? _d($c['stuffing_date']) : '&nbsp;'; ?></td>
                            <td class="container-td" style="height: 20px; border-top: none; border-left: none; border-right: 1px solid #000; <?php echo $b_bottom; ?>"><?php echo isset($c['size']) ? htmlspecialchars($c['size']) : '&nbsp;'; ?></td>
                            <td class="container-td" style="height: 20px; border-top: none; border-left: none; border-right: 1px solid #000; <?php echo $b_bottom; ?>"><?php echo isset($c['shipping_line_seal_no']) ? htmlspecialchars($c['shipping_line_seal_no']) : '&nbsp;'; ?></td>
                            <td class="container-td" style="height: 20px; border-top: none; border-left: none; border-right: 1px solid #000; <?php echo $b_bottom; ?>"><?php echo isset($c['rfid_seal_no']) ? htmlspecialchars($c['rfid_seal_no']) : '&nbsp;'; ?></td>
                            <td class="container-td" style="height: 20px; border-top: none; border-left: none; border-right: 1px solid #000; <?php echo $b_bottom; ?>"><?php echo isset($c['total_packages']) ? htmlspecialchars($c['total_packages']) : '&nbsp;'; ?></td>
                            <td class="container-td" style="height: 20px; border-top: none; border-left: none; border-right: 1px solid #000; <?php echo $b_bottom; ?>"><?php echo isset($c['net_weight']) ? htmlspecialchars($c['net_weight']) : '&nbsp;'; ?></td>
                            <td class="container-td" style="height: 20px; border-top: none; border-left: none; border-right: none; <?php echo $b_bottom; ?>"><?php echo isset($c['gross_weight']) ? htmlspecialchars($c['gross_weight']) : '&nbsp;'; ?></td>
                        </tr>
                    <?php } ?>
                </table>
            </td>
        </tr>

        <!-- Declaration -->
        <tr>
            <td class="vertical-text" text-rotate="90">Declaration</td>
            <td colspan="2" class="declaration-box">
                <?php echo nl2br(htmlspecialchars($declaration)); ?>
            </td>
        </tr>

        <!-- Signature -->
        <tr>
            <td colspan="3" class="signature-box">
                <?php
                $company_signature_path = 'uploads/company/' . get_option('signature_image');
                if (file_exists($company_signature_path) && !empty(get_option('signature_image'))) {
                ?>
                    <img src="<?= base_url('uploads/company/' . get_option('signature_image')) ?>" alt="Company Signature" width="140" height="130"><br>
                <?php
                }
                ?>
                Signature of Exporter
            </td>
        </tr>
    </table>