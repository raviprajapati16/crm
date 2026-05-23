<!DOCTYPE html>
<html>

<head>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 20px;
            line-height: 1.4;
        }

        .receipts-section {
            margin-top: 30px;
            padding-top: 20px;
            page-break-before: always !important;
            margin-bottom: 20px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding-bottom: 20px;
        }

        .report-title {
            font-size: 16px;
            font-weight: bold;
            color: #333;
            text-align: center;
        }

        .report-type {
            color: #666;
            font-size: 14px;
            margin-top: 0px;
        }

        .submitted-by {
            margin: 10px 10px;
        }

        .submitted-by p {
            margin: 2px 0;
            font-size: 11px;
        }

        .expense-table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }

        .expense-table th {
            background-color: #4a4a4a;
            color: white;
            padding: 12px 8px;
            text-align: left;
            font-weight: bold;
            font-size: 11px;
        }

        .expense-table td {
            padding: 12px 8px;
            border-bottom: 1px solid #eee;
            font-size: 11px;
            vertical-align: top;
        }

        .expense-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .expense-details {
            font-weight: bold;
            margin-bottom: 5px;
        }

        .expense-meta {
            color: #666;
            font-size: 10px;
            line-height: 1.3;
        }

        .amount {
            text-align: right;
            font-weight: bold;
            color: #333;
        }

        .summary-section {
            margin-top: 10px;
            padding-top: 10px;
        }

        .summary-table {
            width: 100%;
            margin: 10px 0;
        }

        .summary-table td {
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }

        .summary-table .label {
            font-weight: bold;
            width: 70%;
        }

        .summary-table .value {
            text-align: right;
            font-weight: bold;
            width: 30%;
        }

        .total-row {
            background-color: #f0f0f0;
            font-size: 14px;
        }

        .total-row td {
            padding: 12px 0;
            border-top: 2px solid #333;
            font-weight: bold;
        }

        .signature-section {
            text-align: left;
        }
        
    </style>
</head>

<body>
    <!-- Header Section -->
    <div class="header">
        <div class="report-title">Expense Report</div>
    </div>

    <?php
    $staffData = get_staff($data['created_by']);
    $submitterEmail = $staffData->email;
    $submitterName = $staffData->firstname . ' ' . $staffData->lastname;
    ?>

    <!-- Report Info -->
    <div class="report-type">
        <table style="width: 100%; border-collapse: collapse;">
            <tbody>
                <tr>
                    <td style="width: 60%; padding: 6px 8px;">
                        <strong>Report ID :</strong> #<?= expenseReportIdFormat($data['id']); ?><br>
                        <strong>Report Name :</strong> <?= $data['report_name']; ?><br>
                        <strong>Business Purpose :</strong> <?= ucfirst($data['business_purpose']); ?><br>
                        <strong>Submitted By :</strong> <?= $submitterName; ?> (<?= $submitterEmail; ?>)
                    </td>
                    <td style="width: 40%; padding: 6px 8px;">
                        <?php if (!empty($data['trip_data'])) { ?>
                            <strong>Trip ID :</strong> #<?= expenseTripIdFormat($data['trip_data']['id']);?><br>
                            <strong>Trip Name :</strong> <?= $data['trip_data']['name'];?>
                            <?php if (!empty($data['trip_data']['type'])) { ?>
                                <br>
                                <strong>Type :</strong> <?= ucfirst($data['trip_data']['type']);?>
                                <?php if ($data['trip_data']['type'] == "international" && !empty($data['trip_data']['country_name'])) { ?>
                                    <br>
                                    <strong>Country :</strong> <?= $data['trip_data']['country_name']; ?> (<strong>Visa Required :</strong>   <?= $data['trip_data']['visa_required'] ? 'Yes' : 'No'; ?>)
                            <?php } ?>
                                <?php } ?>
                        <?php } ?>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Expense Summary Table -->
    <?php
    $total_amount = 0;
    $reimbursement_amount = 0;
    ?>
    <h3>EXPENSE SUMMARY</h3>
    <table class="expense-table">
        <thead>
            <tr>
                <th style="width: 8%">S.No</th>
                <th style="width: 42%">Expense Details</th>
                <th style="width: 30%">Category</th>
                <th style="width: 20%">Amount</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $srno = 1;
            foreach ($data['expenses_data'] as $expense) {
                $category = getExpenseCategory($expense['category']);
                $merchant = getExpenseMerchant($expense['merchant_id']);
                $total_amount += $expense['amount'];
                if (isset($expense['reimbursement']) && $expense['reimbursement'] == 1) {
                    $reimbursement_amount += $expense['amount'];
                }
            ?>
                <tr>
                    <td><?= $srno++ ?>.</td>
                    <td>
                        <div class="expense-details"><?= _d($expense['date']) ?></div>
                        <div class="expense-meta">
                            Merchant: <?= $merchant['name'] ?><br>
                            Expense Location: <?= getExpenseLocation($expense['expense_city']) ?><br>
                            Billed: <?= ($expense['billable'] == 1) ? 'Yes' : 'No'  ?><br>
                            <?= ($expense['reference']) ? "Reference: ".$expense['reference'] : ""  ?><br>
                        </div>
                    </td>
                    <td>
                        <?= $category['name'] ?><br>
                        <span style="color: #666; font-size: 10px;"><?= $expense['note'] ?></span>
                    </td>
                    <td class="amount"><?= app_format_money($expense['amount'], get_base_currency()) ?></td>
                </tr>
            <?php
            }
            ?>
        </tbody>
    </table>

    <!-- Summary Section -->
    <?php
    $advance_amount = 0;
    if (!empty($data['expense_advances'])) {
        foreach ($data['expense_advances'] as $advance) {
            $advance_amount += $advance['amount'];
        }
    }

    $non_reimbursable_amount = $total_amount - $reimbursement_amount;
    $final_reimbursement = $reimbursement_amount - $advance_amount;
    ?>
    <div class="summary-section">
        <h3>REPORT SUMMARY</h3>
        <table class="summary-table">
            <tr>
                <td class="label">Total Expense Amount</td>
                <td></td>
                <td class="value"><?= app_format_money($total_amount, get_base_currency()) ?></td>
            </tr>
            <tr>
                <td class="label">Non Reimbursable Amount</td>
                <td></td>
                <td class="value">(-) <?= app_format_money($non_reimbursable_amount, get_base_currency()) ?></td>
            </tr>
            <tr>
                <td class="label">Advance Amount Received</td>
                <td></td>
                <td class="value">(-) <?= app_format_money($advance_amount, get_base_currency()) ?></td>
            </tr>
            <tr class="total-row">
                <td class="label">Total Reimbursable Amount</td>
                <td></td>
                <td class="value"><?= app_format_money($final_reimbursement, get_base_currency()) ?></td>
            </tr>
        </table>

        <table class="signature-section" style="width: 100%;">
            <tr>
                <td style="width: 50%; vertical-align: bottom;">
                    <p><strong><u>Submitted By</u></strong></p>
                </td>
                <?php if ($data['status'] == "Approved" || $data['status'] == "Reimbursed") { ?>
                    <td style="width: 50%; vertical-align: bottom;">
                        <p><strong><u>Approved By</u></strong></p>
                    </td>
                <?php } ?>
            </tr>
            <tr>
                <td style="width: 50%; vertical-align: bottom; height: 100px;">
                    <div style="width: 80%; margin-bottom: 5px;"></div>
                    <hr>
                    <p style="margin: 0;"><strong><?= $submitterName; ?></strong></p>
                </td>
                <?php if ($data['status'] == "Approved" || $data['status'] == "Reimbursed") {
                    $staffData = get_staff($data['status_updated_by']);
                    $approverName = $staffData->firstname . ' ' . $staffData->lastname;
                ?>
                    <td style="width: 50%; vertical-align: bottom; height: 100px;">
                        <div style="width: 80%; margin-bottom: 5px;"></div>
                        <hr>
                        <p style="margin: 0;"><strong><?= $approverName; ?></strong></p>
                    </td>
                <?php } ?>
            </tr>
        </table>
    </div>

    <!-- Receipt Section -->
    <?php if (!empty($data['expenses_data'])) { ?>
        <div class="receipts-section">
            <h3>EXPENSE RECEIPTS</h3>
            <?php
            $all_receipts = [];
            foreach ($data['expenses_data'] as $expense) {
                $category = getExpenseCategory($expense['category']);
                $merchant = getExpenseMerchant($expense['merchant_id']);

                if (!empty($expense['receipts'])) {
                    foreach ($expense['receipts'] as $receipt_img) {
                        $all_receipts[] = [
                            'image' => $receipt_img,
                            'date' => $expense['date'],
                            'amount' => $expense['amount'],
                            'category' => $category['name'],
                            'merchant' => $merchant['name'],
                            'reference' => $expense['reference']
                        ];
                    }
                } else {
                    // Add entry even if no receipt image
                    $all_receipts[] = [
                        'image' => null,
                        'date' => $expense['date'],
                        'amount' => $expense['amount'],
                        'category' => $category['name'],
                        'merchant' => $merchant['name'],
                        'reference' => $expense['reference']
                    ];
                }
            }

            if (!empty($all_receipts)) {
                $receipt_chunks = array_chunk($all_receipts, 3);

                foreach ($receipt_chunks as $receipt_row) {
            ?>
                    <table width="100%" cellpadding="10" cellspacing="0">
                        <tr>
                            <?php
                            $count = 0;
                            foreach ($receipt_row as $receipt) {
                                if ($count > 0 && $count % 3 == 0) {
                                    echo '</tr><tr>'; // Start a new row after every 3 receipts
                                }
                            ?>
                                <td width="33.33%" valign="top" style="border: 1px solid #000;">
                                    <?php if (!empty($receipt['image'])) { ?>
                                        <img src="<?= $receipt['image'] ?>" alt="Receipt" style="width: 350px; height: 450px; display: block; margin: 0 auto;">
                                    <?php } else { ?>
                                        <img src="<?= site_url('assets/images/no-receipt.jpg') ?>" alt="Receipt" style="width: 350px; height: 450px; display: block; margin: 0 auto;">
                                    <?php } ?>
                                    <br>
                                    <div style="font-size: 15px; text-align: left !important;">
                                        <strong>Date:</strong> <?= _d($receipt['date']) ?><br>
                                        <strong>Amount:</strong> <?= app_format_money($receipt['amount'], get_base_currency()) ?><br>
                                        <strong>Category:</strong> <?= $receipt['category'] ?><br>
                                        <strong>Merchant:</strong> <?= $receipt['merchant'] ?><br>
                                    </div>
                                </td>
                            <?php
                                $count++;
                            }
                            // Fill remaining cells if not multiple of 3
                            while ($count % 3 != 0) {
                                echo '<td width="33.33%"></td>';
                                $count++;
                            }
                            ?>
                        </tr>
                    </table>



                <?php
                }
            } else {
                ?>
                <div class="no-receipts">No receipts available for this expense report.</div>
            <?php
            }
            ?>
        </div>
    <?php } ?>
</body>

</html>