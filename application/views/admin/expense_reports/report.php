<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<style>
        #loadingOverlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: 9999;
        /* Hidden by default */
        align-items: center;
        justify-content: center;
    }
    .timeline {
        position: relative;
        padding-left: 30px;
    }

    .timeline-item {
        position: relative;
        margin-bottom: 20px;
    }

    .timeline-marker {
        position: absolute;
        left: -35px;
        top: 5px;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background-color: #ddd;
        border: 2px solid #fff;
        box-shadow: 0 0 0 2px #ddd;
    }

    .timeline-marker-success {
        background-color: #5cb85c;
        box-shadow: 0 0 0 2px #5cb85c;
    }

    .timeline:before {
        content: '';
        position: absolute;
        left: -30px;
        top: 10px;
        bottom: -10px;
        width: 2px;
        background-color: #ddd;
    }

    .timeline-content {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 4px;
        border-left: 3px solid #007bff;
    }

    .timeline-title {
        margin: 0 0 5px 0;
        font-weight: 600;
    }

    .timeline-description {
        margin: 0 0 5px 0;
        color: #666;
    }

    #advanceList .checkbox,
    #expenseList .checkbox {
        margin-bottom: 15px;
        padding: 10px;
        border: 1px solid #e7e7e7;
        border-radius: 4px;
        background-color: #f9f9f9;
    }

    #advanceList .checkbox:hover,
    #expenseList .checkbox:hover {
        background-color: #f5f5f5;
    }

    #advanceList .checkbox label,
    #expenseList .checkbox label {
        margin-bottom: 0;
        font-weight: normal;
        cursor: pointer;
        width: 100%;
    }

    .remove-advance-btn,
    .remove-expense-btn {
        padding: 2px 6px;
        font-size: 11px;
        margin-left: 5px;
    }

    .advance-row:hover .remove-advance-btn,
    .expense-row:hover .remove-expense-btn {
        opacity: 1;
    }

    .remove-advance-btn,
    .remove-expense-btn {
        opacity: 0.7;
        transition: opacity 0.2s;
    }

    /* Custom Tab Styles */
    .custom-tabs {
        margin-top: 30px;
    }

    .custom-tabs .nav-tabs {
        border-bottom: 2px solid #ddd;
        margin-bottom: 20px;
    }

    .custom-tabs .nav-tabs>li>a {
        border: none;
        border-radius: 0;
        color: #666;
        font-weight: 500;
        padding: 12px 20px;
        margin-right: 5px;
        position: relative;
    }

    .custom-tabs .nav-tabs>li>a:hover {
        background-color: #f8f9fa;
        border: none;
        color: #333;
    }

    .custom-tabs .nav-tabs>li.active>a,
    .custom-tabs .nav-tabs>li.active>a:hover,
    .custom-tabs .nav-tabs>li.active>a:focus {
        background-color: transparent;
        border: none;
        color: #007bff;
        border-bottom: 2px solid #007bff;
    }

    .custom-tabs .tab-content {
        padding: 0;
    }

    .tab-badge {
        background-color: #007bff;
        color: white;
        border-radius: 12px;
        padding: 2px 8px;
        font-size: 11px;
        margin-left: 5px;
        font-weight: normal;
    }

    .tab-badge.badge-success {
        background-color: #28a745;
    }

    .tab-badge.badge-warning {
        background-color: #ffc107;
        color: #333;
    }

    .approval-actions {
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        padding: 15px;
        margin-top: 20px;
    }

    .approval-actions button {
        margin-right: 10px;
    }

    .approval-actions h5 {
        margin-top: 0;
        color: #495057;
        text-align: center;
    }

    .report-btn {
        margin-top: 5px;
    }
</style>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <!-- Report Header -->
                        <div class="row">
                            <div class="col-md-8">
                                <h4 class="no-margin"><?php echo isset($report['report_name']) ? $report['report_name'] : ''; ?></h4>
                                <p class="text-muted"><?php echo isset($report['id']) ? "#" . expenseReportIdFormat($report['id']) : ''; ?></p>

                                <p class="text-muted">Duration: <?php echo _d($report['start_date']) . ' - ' . _d($report['end_date']); ?></p>

                                <!-- Business Purpose and Trip Info -->
                                <div class="mtop15">
                                    <p><strong>Business Purpose:</strong> <?php echo isset($report['business_purpose']) ? $report['business_purpose'] : ''; ?></p>
                                    <p><strong>Trip:</strong> <?php echo !empty($trip) ? "#" . expenseTripIdFormat($trip['id']) . " - " . $trip['name'] : ''; ?></p>
                                </div>
                            </div>
                            <div class="col-md-4 text-right">
                                <!-- Report Status -->
                                <?php if (isset($report['status'])) { ?>
                                    <span class="label label-<?php
                                                                echo $report['status'] == 'Draft' ? 'default' : ($report['status'] == 'submitted' ? 'info' : ($report['status'] == 'Approved' || $report['status'] == 'Reimbursed' ? 'success' : ($report['status'] == 'Rejected' ? 'danger' : ($report['status'] == 'Awaiting Approval' ? 'warning' : 'default'))));
                                                                ?>">
                                        <?php echo strtoupper($report['status']); ?>
                                    </span>
                                    <?php

                                    if ($report['status'] == 'Approved' || $report['status'] == 'Reimbursed' || $report['status'] == 'Rejected') { ?>
                                        <br><br>
                                        <p class="text-muted">By : <?php echo isset($report['status_updated_by']) ? get_staff_full_name($report['status_updated_by']) : ''; ?> <br> <?= _dt($report['status_updated_at']) ?></p>
                                        <br>
                                        <?php if ($report['status'] == "Rejected") {
                                        ?>
                                            <p class="text-muted"><b>Rejection Reason:</b> <br><?php echo isset($report['rejection_reason']) ? $report['rejection_reason'] : ''; ?></p>
                                        <?php
                                        }
                                        ?>
                                    <?php
                                    }
                                    ?>

                                <?php } ?>
                            </div>
                            <!-- Replace the existing approval buttons section with this -->
                            <div class="col-md-4 text-center">
                                <?php if ($report['status'] == 'Awaiting Approval' && (is_admin() || has_permission('expense_reports', '', 'approve_reject_report'))): ?>
                                    <div class="approval-actions">
                                        <h5>Actions</h5>
                                        <div class="btn-group">
                                            <button type="button"
                                                id="approveBtn"
                                                class="btn btn-success"
                                                onclick="approveReport()"
                                                data-action="approve">
                                                <i class="fa fa-check"></i> Approve
                                            </button>
                                            <button type="button"
                                                id="rejectBtn"
                                                class="btn btn-danger"
                                                onclick="rejectReport()"
                                                data-action="reject">
                                                <i class="fa fa-times"></i> Reject
                                            </button>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                <?php if ((has_permission('expense_reports', '', 'approve_reject_report') || is_admin())  && $report['status'] == 'Approved' && $final_reimbursement !== 0): ?>
                                    <div class="approval-actions">
                                        <h5>Reimbursement</h5>
                                        <button type="button"
                                            id="recordReimbursementBtn"
                                            class="btn btn-primary"
                                            onclick="openReimbursementModal()">
                                            Record Reimbursement
                                        </button>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Tab Navigation -->
                        <div class="custom-tabs">
                            <ul class="nav nav-tabs" role="tablist">
                                <li role="presentation" class="active">
                                    <a href="#expenses-tab" aria-controls="expenses-tab" role="tab" data-toggle="tab">
                                        <i class="fa fa-receipt"></i> Expenses
                                        <?php if (!empty($expenses)): ?>
                                            <span class="tab-badge"><?php echo count($expenses); ?></span>
                                        <?php endif; ?>
                                    </a>
                                </li>
                                <li role="presentation">
                                    <a href="#advances-tab" aria-controls="advances-tab" role="tab" data-toggle="tab">
                                        <i class="fa fa-money"></i> Advance Payments
                                        <?php if (!empty($expense_advances)): ?>
                                            <span class="tab-badge badge-success"><?php echo count($expense_advances); ?></span>
                                        <?php endif; ?>
                                    </a>
                                </li>
                                <li role="presentation">
                                    <a href="#summary-tab" aria-controls="summary-tab" role="tab" data-toggle="tab">
                                        <i class="fa fa-calculator"></i> Summary
                                    </a>
                                </li>

                                <?php if (!empty($reimbursement)): ?>
                                    <li role="presentation">
                                        <a href="#reimbursement-tab" aria-controls="reimbursement-tab" role="tab" data-toggle="tab">
                                            <i class="fa fa-credit-card"></i> Reimbursement
                                            <i class="fa fa-check text-success"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>


                                <div class="btn-group report-btn pull-right">
                                    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="fa fa-file-pdf-o"></i> Report <span class="caret"></span>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a href="#" onclick="generatePDF('view')">View</a></li>
                                        <li><a href="#" onclick="generatePDF('download')">Download</a></li>
                                    </ul>
                                </div>
                            </ul>

                            <!-- Tab Content -->
                            <div class="tab-content">
                                <!-- Expenses Tab -->
                                <div role="tabpanel" class="tab-pane active" id="expenses-tab">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <!-- Add this in the expenses tab, after the table div -->
                                            <div class="row">
                                                <div class="col-md-12 text-right">
                                                    <?php if (($report['status'] == 'Draft' || $report['status'] == 'Rejected') && count($unreported_expenses)): ?>
                                                        <button type="button" class="btn btn-primary btn-sm" onclick="openAddExpenseModal()">
                                                            <i class="fa fa-plus"></i> Add Expenses (<?= count($unreported_expenses) ?>)
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <?php if (!empty($expenses)): ?>
                                                <div class="table-responsive">
                                                    <table class="table table-striped table-bordered">
                                                        <thead>
                                                            <tr>
                                                                <th>Expense ID</th>
                                                                <th>Date</th>
                                                                <th>Category</th>
                                                                <th>Merchant</th>
                                                                <th>Amount</th>
                                                                <th>Reimbursable</th>
                                                                <th>Billed</th>
                                                                <th>Receipt</th>
                                                                <?php if ($report['status'] == 'Draft' || $report['status'] == 'Rejected'): ?>
                                                                    <th width="80">Action</th>
                                                                <?php endif; ?>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php
                                                            $total_amount = 0;
                                                            $reimbursement_amount = 0;
                                                            foreach ($expenses as $expense):
                                                                $category = getExpenseCategory($expense['category']);
                                                                $merchant = getExpenseMerchant($expense['merchant_id']);
                                                                $total_amount += $expense['amount'];
                                                                if (isset($expense['reimbursement']) && $expense['reimbursement'] == 1) {
                                                                    $reimbursement_amount += $expense['amount'];
                                                                }
                                                            ?>
                                                                <tr>
                                                                    <td>#<?php echo expenseIdFormat($expense['id']); ?></td>
                                                                    <td><?php echo _d($expense['date']); ?></td>
                                                                    <td><?php echo ($category['name']) ? $category['name'] : ""  ?></td>
                                                                    <td><?php echo ($merchant['name']) ? $merchant['name'] : ""  ?></td>
                                                                    <td><?php echo app_format_money($expense['amount'], get_base_currency()); ?></td>
                                                                    <td>
                                                                        <?php if (isset($expense['reimbursement']) && $expense['reimbursement'] == 1): ?>
                                                                            <span class="label label-success">Yes</span>
                                                                        <?php else: ?>
                                                                            <span class="label label-default">No</span>
                                                                        <?php endif; ?>
                                                                    </td>
                                                                    <td>
                                                                        <?php if (isset($expense['billable']) && $expense['billable'] == 1): ?>
                                                                            <span class="label label-success">Yes</span>
                                                                        <?php else: ?>
                                                                            <span class="label label-default">No</span>
                                                                        <?php endif; ?>
                                                                    </td>
                                                                    <td>
                                                                        <?php if (!empty($expense['attachment'])): ?>
                                                                            <a href="<?php echo $expense['attachment']; ?>" target="_blank" class="btn btn-xs btn-info">
                                                                                <i class="fa fa-eye"></i> View
                                                                            </a>
                                                                        <?php else: ?>
                                                                            <span class="text-muted">No receipt</span>
                                                                        <?php endif; ?>
                                                                    </td>
                                                                    <?php if ($report['status'] == 'Draft' || $report['status'] == 'Rejected'): ?>
                                                                        <td>
                                                                            <button type="button"
                                                                                class="btn btn-xs btn-danger remove-expense-btn"
                                                                                onclick="removeExpense(<?php echo $expense['id']; ?>)"
                                                                                title="Remove expense from report">
                                                                                <i class="fa fa-times"></i>
                                                                            </button>
                                                                        </td>
                                                                    <?php endif; ?>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            <?php else: ?>
                                                <div class="text-center mtop30 mbot30">
                                                    <i class="fa fa-receipt fa-3x text-muted"></i>
                                                    <h4 class="text-muted">No expenses added yet</h4>
                                                    <p class="text-muted">Add expenses to this report to continue</p>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- Advances Tab -->
                                <div role="tabpanel" class="tab-pane" id="advances-tab">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="row">
                                                <div class="col-md-12 text-right">
                                                    <?php if (($report['status'] == 'Draft' || $report['status'] == 'Rejected') && count($unreported_advances)): ?>
                                                        <button type="button" class="btn btn-primary btn-sm" onclick="openAddAdvanceModal()">
                                                            <i class="fa fa-plus"></i> Apply Advance (<?= count($unreported_advances) ?>)
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <?php if (!empty($expense_advances)): ?>
                                                <div class="table-responsive">
                                                    <table class="table table-striped table-bordered">
                                                        <thead>
                                                            <tr>
                                                                <th>Adv. Payment ID</th>
                                                                <th>Date</th>
                                                                <th>#Reference</th>
                                                                <th>Amount</th>
                                                                <?php if ($report['status'] == 'Draft' || $report['status'] == 'Rejected'): ?>
                                                                    <th width="80">Action</th>
                                                                <?php endif; ?>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php foreach ($expense_advances as $advance): ?>
                                                                <tr class="advance-row" data-advance-id="<?php echo $advance['id']; ?>">
                                                                    <td><?php echo expenseAdvancePaymentIdFormat($advance['id']); ?></td>
                                                                    <td><?php echo _d($advance['date']); ?></td>
                                                                    <td><?php echo $advance['reference']; ?></td>
                                                                    <td><?php echo app_format_money($advance['amount'], get_base_currency()); ?></td>
                                                                    <?php if ($report['status'] == 'Draft' || $report['status'] == 'Rejected'): ?>
                                                                        <td>
                                                                            <button type="button"
                                                                                class="btn btn-xs btn-danger remove-advance-btn"
                                                                                onclick="removeAdvance(<?php echo $advance['id']; ?>)"
                                                                                title="Remove advance from report">
                                                                                <i class="fa fa-times"></i>
                                                                            </button>
                                                                        </td>
                                                                    <?php endif; ?>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            <?php else: ?>
                                                <div class="text-center mtop30 mbot30">
                                                    <i class="fa fa-money fa-3x text-muted"></i>
                                                    <h4 class="text-muted">No advances found</h4>
                                                    <p class="text-muted">No advances are associated with this report</p>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- Summary Tab -->
                                <div role="tabpanel" class="tab-pane" id="summary-tab">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="panel panel-default">
                                                <div class="panel-heading">
                                                    <h4 class="panel-title">Financial Summary</h4>
                                                </div>
                                                <div class="panel-body">
                                                    <?php
                                                    $advance_amount = 0;
                                                    if (!empty($expense_advances)) {
                                                        foreach ($expense_advances as $advance) {
                                                            $advance_amount += $advance['amount'];
                                                        }
                                                    }

                                                    $non_reimbursable_amount = $total_amount - $reimbursement_amount;

                                                    $final_reimbursement = $reimbursement_amount - $advance_amount;
                                                    ?>

                                                    <div class="row">
                                                        <div class="col-md-8">
                                                            <table class="table table-borderless" style="margin-bottom: 0;">
                                                                <tr>
                                                                    <td style="border: none; padding: 8px 0;"><strong>Total Expense Amount</strong></td>
                                                                    <td style="border: none; padding: 8px 0; text-align: right; font-size: 16px;">
                                                                        <?php echo app_format_money($total_amount, get_base_currency()); ?>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td style="border: none; padding: 8px 0;"><strong>Non-reimbursable Amount</strong></td>
                                                                    <td style="border: none; padding: 8px 0; text-align: right; font-size: 16px;">
                                                                        (-) <?php echo app_format_money($non_reimbursable_amount, get_base_currency()); ?>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td style="border: none; padding: 8px 0;"><strong>Applied Advance Amount</strong></td>
                                                                    <td style="border: none; padding: 8px 0; text-align: right; font-size: 16px;">
                                                                        (-) <?php echo app_format_money($advance_amount, get_base_currency()); ?>
                                                                    </td>
                                                                </tr>
                                                                <tr style="border-top: 2px solid #ddd;">
                                                                    <td style="border: none; padding: 15px 0 0 0;"><strong>Amount to be Reimbursed</strong></td>
                                                                    <td style="border: none; padding: 15px 0 0 0; text-align: right; font-size: 18px; font-weight: bold;">
                                                                        <span class="<?php echo $final_reimbursement >= 0 ? 'text-success' : 'text-danger'; ?>">
                                                                            <?php echo app_format_money($final_reimbursement, get_base_currency()); ?>
                                                                        </span>
                                                                    </td>
                                                                </tr>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Reimbursement Tab -->
                                <?php if (!empty($reimbursement)): ?>
                                    <div role="tabpanel" class="tab-pane" id="reimbursement-tab">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="panel panel-default">
                                                    <div class="panel-heading">
                                                        <h4 class="panel-title">
                                                            <i class="fa fa-credit-card"></i> Reimbursement Details
                                                        </h4>
                                                    </div>
                                                    <div class="panel-body">
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <div class="well" style="background-color: #f8f9fa; border-left: 4px solid #28a745;">
                                                                    <h5 style="margin-top: 0; color: #28a745;">
                                                                        <i class="fa fa-check-circle"></i>
                                                                        <?php echo $reimbursement['type'] == 'reimbursed' ? 'Payment Completed' : 'Refund Received'; ?>
                                                                    </h5>
                                                                    <p class="text-muted" style="margin-bottom: 0;">
                                                                        This expense report has been successfully
                                                                        <?php echo $reimbursement['type']; ?>
                                                                        on <?php echo _d($reimbursement['date']); ?>.
                                                                    </p>
                                                                </div>
                                                                <table class="table table-borderless">
                                                                    <tr>
                                                                        <td style="border: none; padding: 8px 0; width: 40%;"><strong>Amount:</strong></td>
                                                                        <td style="border: none; padding: 8px 0;">
                                                                            <span class="text-success" style="font-size: 18px; font-weight: bold;">
                                                                                <?php echo app_format_money($reimbursement['amount'], get_base_currency()); ?>
                                                                            </span>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td style="border: none; padding: 8px 0;"><strong>Date:</strong></td>
                                                                        <td style="border: none; padding: 8px 0;">
                                                                            <?php echo _d($reimbursement['date']); ?>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td style="border: none; padding: 8px 0;"><strong>Status:</strong></td>
                                                                        <td style="border: none; padding: 8px 0;">
                                                                            <span class="label label-success">
                                                                                <?php echo ucfirst($reimbursement['type']); ?>
                                                                            </span>
                                                                        </td>
                                                                    </tr>
                                                                    <?php if (!empty($reimbursement['paid_through'])): ?>
                                                                        <tr>
                                                                            <td style="border: none; padding: 8px 0;"><strong>Payment Method:</strong></td>
                                                                            <td style="border: none; padding: 8px 0;">
                                                                                <?php
                                                                                echo get_payment_mode_name($reimbursement['paid_through']);
                                                                                ?>
                                                                            </td>
                                                                        </tr>
                                                                    <?php endif; ?>
                                                                    <tr>
                                                                        <td style="border: none; padding: 8px 0;"><strong>Reference:</strong></td>
                                                                        <td style="border: none; padding: 8px 0;">
                                                                            <?php echo $reimbursement['reference']; ?>
                                                                        </td>
                                                                    </tr>
                                                                    <?php if (!empty($reimbursement['notes'])): ?>
                                                                        <tr>
                                                                            <td style="border: none; padding: 8px 0; vertical-align: top;"><strong>Notes:</strong></td>
                                                                            <td style="border: none; padding: 8px 0;">
                                                                                <?php echo nl2br(htmlspecialchars($reimbursement['notes'])); ?>
                                                                            </td>
                                                                        </tr>
                                                                    <?php endif; ?>
                                                                    <tr>
                                                                        <td style="border: none; padding: 8px 0;"><strong>Processed By:</strong></td>
                                                                        <td style="border: none; padding: 8px 0;">
                                                                            <?php echo get_staff_full_name($reimbursement['created_by']); ?>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td style="border: none; padding: 8px 0;"><strong>Processed On:</strong></td>
                                                                        <td style="border: none; padding: 8px 0;">
                                                                            <?php echo _dt($reimbursement['created_at']); ?>
                                                                        </td>
                                                                    </tr>
                                                                </table>
                                                                <?php if (has_permission('expense_reports', '', 'approve_reject_report') || is_admin()): ?>
                                                                    <tr>
                                                                        <td style="border: none; padding: 8px 0;" colspan="2">
                                                                            <button type="button" class="btn btn-danger btn-sm" onclick="undoReimbursement(<?php echo $reimbursement['id']; ?>)">
                                                                                <i class="fa fa-undo"></i> Undo Reimbursement
                                                                            </button>
                                                                        </td>
                                                                    </tr>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="row mtop30">
                            <div class="col-md-12">
                                <?php if ($report['created_by'] == get_staff_user_id() && count($expenses) != 0) { ?>
                                    <div class="text-right">
                                        <?php if ($report['status'] == 'Draft'): ?>
                                            <button type="button" class="btn btn-success" onclick="submitReport()">
                                                Submit Report
                                            </button>
                                        <?php endif; ?>
                                        <?php if ($report['status'] == 'Rejected'): ?>
                                            <button type="button" class="btn btn-success" onclick="submitReport()">
                                                Re-Submit Report
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Reject Report Modal -->
<div class="modal fade" id="rejectReportModal" tabindex="-1" role="dialog" aria-labelledby="rejectReportModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="rejectReportModalLabel">Reject Report</h4>
            </div>
            <form id="rejectReportForm">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="rejectionReason">Reason for Rejection <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="rejectionReason" name="rejection_reason" rows="4"
                            placeholder="Please provide a reason for rejecting this report..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Reject Report</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- Add Advance Modal -->
<div class="modal fade" id="addAdvanceModal" tabindex="-1" role="dialog" aria-labelledby="addAdvanceModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="addAdvanceModalLabel">Add Advance Payments</h4>
            </div>
            <form id="addAdvanceForm">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="advanceSelect">Select Advance Payments</label>
                        <?php if (!empty($unreported_advances)): ?>
                            <div class="form-group">
                                <div class="checkbox">
                                    <input type="checkbox" id="selectAllAdvances">
                                    <label for="selectAllAdvances">Select All</label>
                                </div>
                            </div>
                        <?php endif; ?>
                        <div id="advanceList">
                            <?php if (!empty($unreported_advances)): ?>
                                <?php foreach ($unreported_advances as $key => $advance): ?>
                                    <div class="checkbox">
                                        <div class="checkbox-primary reimbursement">
                                            <input type="checkbox" id="chk_<?= $key ?>" name="selected_advances[]" value="<?php echo $advance['id']; ?>">
                                            <label for="chk_<?= $key ?>">
                                                <strong><?php echo expenseAdvancePaymentIdFormat($advance['id']); ?></strong> -
                                                <?php echo _d($advance['date']); ?> -
                                                <span class="text-success"><?php echo app_format_money($advance['amount'], get_base_currency()); ?></span>
                                                <?php if (!empty($advance['reference'])): ?>
                                                    <br><small class="text-muted">Ref: <?php echo $advance['reference']; ?></small>
                                                <?php endif; ?></label>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="text-center text-muted">
                                    <p>No unreported advance payments available</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <?php if (!empty($unreported_advances)): ?>
                        <button type="submit" class="btn btn-primary">Add Selected Advances</button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Remove Advance Confirmation Modal -->
<div class="modal fade" id="removeAdvanceModal" tabindex="-1" role="dialog" aria-labelledby="removeAdvanceModalLabel">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="removeAdvanceModalLabel">Remove Advance</h4>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to remove this advance from the report?</p>
                <p><small class="text-muted">This will make the advance available for other reports.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmRemoveAdvance">Remove</button>
            </div>
        </div>
    </div>
</div>

<!-- Add Expense Modal -->
<div class="modal fade" id="addExpenseModal" tabindex="-1" role="dialog" aria-labelledby="addExpenseModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="addExpenseModalLabel">Add Expenses</h4>
            </div>
            <form id="addExpenseForm">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="expenseSelect">Select Expenses</label>
                        <?php if (!empty($unreported_expenses)): ?>
                            <div class="form-group">
                                <div class="checkbox">
                                    <input type="checkbox" id="selectAllExpenses">
                                    <label for="selectAllExpenses">Select All</label>
                                </div>
                            </div>
                        <?php endif; ?>
                        <div id="expenseList">
                            <?php if (!empty($unreported_expenses)): ?>
                                <?php foreach ($unreported_expenses as $key => $expense):
                                    $category = getExpenseCategory($expense['category']);
                                    $merchant = getExpenseMerchant($expense['merchant_id']);
                                ?>
                                    <div class="checkbox">
                                        <div class="checkbox-primary">
                                            <input type="checkbox" id="exp_chk_<?= $key ?>" name="selected_expenses[]" value="<?php echo $expense['id']; ?>">
                                            <label for="exp_chk_<?= $key ?>">
                                                <strong>#<?php echo expenseIdFormat($expense['id']); ?></strong> -
                                                <?php echo _d($expense['date']); ?> - <span class="text-success"><?php echo app_format_money($expense['amount'], get_base_currency()); ?></span>
                                                <?php if (!empty($category['name'])): ?>
                                                    <br><small class="text-muted">Category : <?php echo $category['name']; ?></small>
                                                <?php endif; ?>
                                                <?php if (!empty($merchant['name'])): ?>
                                                    <br><small class="text-muted">Merchant : <?php echo $merchant['name']; ?></small>
                                                <?php endif; ?>
                                            </label>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="text-center text-muted">
                                    <p>No unreported expenses available</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <?php if (!empty($unreported_expenses)): ?>
                        <button type="submit" class="btn btn-primary">Add Selected Expenses</button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Remove Expense Confirmation Modal -->
<div class="modal fade" id="removeExpenseModal" tabindex="-1" role="dialog" aria-labelledby="removeExpenseModalLabel">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="removeExpenseModalLabel">Remove Expense</h4>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to remove this expense from the report?</p>
                <p><small class="text-muted">This will make the expense available for other reports.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmRemoveExpense">Remove</button>
            </div>
        </div>
    </div>
</div>


<?php if ((has_permission('expense_reports', '', 'approve_reject_report') || is_admin())  && $report['status'] == 'Approved' && $final_reimbursement !== 0): ?>
    <div class="modal fade" id="recordReimbursementModal" tabindex="-1" role="dialog" aria-labelledby="recordReimbursementModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title" id="recordReimbursementModalLabel">
                        <?php echo $final_reimbursement < 0 ? 'Record Refund Received' : 'Record Reimbursement Paid'; ?>
                    </h4>
                </div>
                <form id="recordReimbursementForm">
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <strong>Amount to be Reimbursed:</strong>
                            <span class="pull-right"><?php echo app_format_money(abs($final_reimbursement), get_base_currency()); ?></span>
                        </div>

                        <div class="form-group">
                            <label for="reimbursementAmount">Amount <span class="text-danger">*</span></label>
                            <input type="number"
                                class="form-control"
                                id="reimbursementAmount"
                                name="amount"
                                value="<?php echo abs($final_reimbursement); ?>"
                                readonly>
                        </div>

                        <div class="form-group">
                            <label for="reimbursementDate">
                                <?php echo $final_reimbursement < 0 ? 'Received Date' : 'Reimbursed Date'; ?>
                                <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                class="form-control datepicker"
                                id="reimbursementDate"
                                name="date"
                                value="<?php echo date('d-m-Y'); ?>"
                                required>
                        </div>

                        <?php if ($final_reimbursement >= 0): ?>
                            <div class="form-group">
                                <label for="paidThrough">Paid Through <span class="text-danger">*</span></label>
                                <select class="form-control selectpicker" data-live-search="true" id="paidThrough" name="paid_through" required>
                                    <?php foreach ($payment_modes as $mode) { ?>
                                        <option value="<?php echo $mode['id']; ?>"><?php echo $mode['name']; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        <?php endif; ?>

                        <div class="form-group">
                            <label for="reimbursementReference">Reference</label>
                            <input type="text"
                                class="form-control"
                                id="reimbursementReference"
                                name="reference"
                                autocomplete="off"
                                placeholder="Transaction ID, Check Number, etc."
                                >
                        </div>

                        <div class="form-group">
                            <label for="reimbursementNotes">Notes</label>
                            <textarea class="form-control"
                                id="reimbursementNotes"
                                name="notes"
                                rows="3"
                                placeholder="Additional notes about the reimbursement..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">
                            Record <?php echo $final_reimbursement < 0 ? 'Refund' : 'Reimbursement'; ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endif; ?>



<!-- Undo Reimbursement Modal -->
<?php if ((has_permission('expense_reports', '', 'approve_reject_report') || is_admin())  && $report['status'] == 'Reimbursed' && !empty($reimbursement)): ?>
    <div class="modal fade" id="undoReimbursementModal" tabindex="-1" role="dialog" aria-labelledby="undoReimbursementModalLabel">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title" id="undoReimbursementModalLabel">Undo Reimbursement</h4>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to undo this reimbursement?</p>
                    <p>This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmUndoReimbursement">Undo</button>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
<?php init_tail(); ?>

<script>
    var advanceToRemove = null;
    var expenseToRemove = null;

    $(function() {

        if ($('.datepicker').length > 0) {
            $('.datepicker').on('keydown', function() {
                return false;
            });
        }

        $('#selectAllAdvances').on('click', function() {
            var isChecked = $(this).prop('checked');
            $('input[name="selected_advances[]"]').each(function() {
                $(this).prop('checked', isChecked);
            });
        });

        $(document).on('click', 'input[name="selected_advances[]"]', function() {
            var totalCheckboxes = $('input[name="selected_advances[]"]').length;
            var checkedCheckboxes = $('input[name="selected_advances[]"]:checked').length;
            $('#selectAllAdvances').prop('checked', checkedCheckboxes === totalCheckboxes);
        });

        $('#addAdvanceForm').submit(function(e) {
            e.preventDefault();

            var selectedAdvances = [];
            $('input[name="selected_advances[]"]:checked').each(function() {
                selectedAdvances.push($(this).val());
            });

            if (selectedAdvances.length === 0) {
                alert_float('warning', 'Please select at least one advance payment');
                return;
            }

            var submitBtn = $(this).find('button[type="submit"]');
            var originalText = submitBtn.text();
            submitBtn.prop('disabled', true).text('Adding...');

            $.post('<?php echo admin_url("expense_reports/add_advances_to_report"); ?>', {
                report_id: <?php echo $report['id']; ?>,
                advance_ids: selectedAdvances
            }, function(response) {
                if (response.success) {
                    alert_float('success', 'Advance payments added successfully');
                    $('#addAdvanceModal').modal('hide');
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                } else {
                    alert_float('danger', response.message || 'Failed to add advance payments');
                }
            }, 'json').fail(function() {
                alert_float('danger', 'Failed to add advance payments');
            }).always(function() {
                submitBtn.prop('disabled', false).text(originalText);
            });
        });

        // Handle remove advance confirmation
        $('#confirmRemoveAdvance').on('click', function() {
            if (advanceToRemove) {
                performRemoveAdvance(advanceToRemove);
            }
        });

        // Tab switch event - update URL hash for bookmarking
        $('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
            var target = $(e.target).attr("href");
            if (target) {
                window.location.hash = target.replace('#', '#tab-');
            }
        });

        $('#rejectReportForm').submit(function(e) {
            e.preventDefault();
            const reason = $('#rejectionReason').val().trim();
            processReport('reject', reason);
        });

        // Clear rejection reason when modal is closed
        $('#rejectReportModal').on('hidden.bs.modal', function() {
            $('#rejectionReason').val('');
        });


        $(document).on('click', '[data-action="approve"]', function() {
            processReport('approve');
        });

        // Direct reject button handler
        $(document).on('click', '[data-action="reject"]', function() {
            $('#rejectReportModal').modal('show');
        });


        // Check for hash in URL on page load
        var hash = window.location.hash;
        if (hash) {
            var tabId = hash.replace('#tab-', '#');
            if ($(tabId).length) {
                $('a[href="' + tabId + '"]').tab('show');
            }
        }


        $('#selectAllExpenses').on('click', function() {
            var isChecked = $(this).prop('checked');
            $('input[name="selected_expenses[]"]').each(function() {
                $(this).prop('checked', isChecked);
            });
        });

        $(document).on('click', 'input[name="selected_expenses[]"]', function() {
            var totalCheckboxes = $('input[name="selected_expenses[]"]').length;
            var checkedCheckboxes = $('input[name="selected_expenses[]"]:checked').length;
            $('#selectAllExpenses').prop('checked', checkedCheckboxes === totalCheckboxes);
        });

        $('#addExpenseForm').submit(function(e) {
            e.preventDefault();

            var selectedExpenses = [];
            $('input[name="selected_expenses[]"]:checked').each(function() {
                selectedExpenses.push($(this).val());
            });

            if (selectedExpenses.length === 0) {
                alert_float('warning', 'Please select at least one expense');
                return;
            }

            var submitBtn = $(this).find('button[type="submit"]');
            var originalText = submitBtn.text();
            submitBtn.prop('disabled', true).text('Adding...');

            $.post('<?php echo admin_url("expense_reports/add_expenses_to_report"); ?>', {
                report_id: <?php echo $report['id']; ?>,
                expense_ids: selectedExpenses
            }, function(response) {
                if (response.success) {
                    alert_float('success', 'Expenses added successfully');
                    $('#addExpenseModal').modal('hide');
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                } else {
                    alert_float('danger', response.message || 'Failed to add expenses');
                }
            }, 'json').fail(function() {
                alert_float('danger', 'Failed to add expenses');
            }).always(function() {
                submitBtn.prop('disabled', false).text(originalText);
            });
        });

        // Handle remove expense confirmation
        $('#confirmRemoveExpense').on('click', function() {
            if (expenseToRemove) {
                performRemoveExpense(expenseToRemove);
            }
        });

        $('#recordReimbursementForm').appFormValidator({
            rules: {
                amount: 'required',
                date: 'required',
                paid_through: {
                    required: function() {
                        return $('#paid_through').length > 0;
                    }
                }
            },
            messages: {
                end_date: {
                    dateGreaterThanOrEqual: "End date cannot be earlier than start date"
                }
            },
            errorPlacement: function(error, element) {
                var formGroup = $(element).closest('.form-group');
                $(formGroup).append(error);
            },
            submitHandler: function(form) {
                var $submitBtn = $('#recordReimbursementForm').find('button[type="submit"]');
                var originalText = $submitBtn.text();
                $submitBtn.prop('disabled', true).text('Recording...');

                var formData = {
                    report_id: <?php echo $report['id']; ?>,
                    amount: $('#reimbursementAmount').val(),
                    date: $('#reimbursementDate').val(),
                    <?php if ($final_reimbursement >= 0): ?>
                        paid_through: $('#paidThrough').val(),
                    <?php endif; ?>
                    reference: $('#reimbursementReference').val(),
                    notes: $('#reimbursementNotes').val(),
                    type: <?php echo $final_reimbursement < 0 ? "'refunded '" : "'reimbursed'"; ?>
                };

                $.post('<?php echo admin_url("expense_reports/record_reimbursement"); ?>', formData, function(response) {
                    if (response.success) {
                        alert_float('success', response.message || 'Reimbursement recorded successfully');
                        $('#recordReimbursementModal').modal('hide');
                        setTimeout(() => {
                            location.reload();
                        }, 1000);
                    } else {
                        alert_float('danger', response.message || 'Failed to record reimbursement');
                    }
                }, 'json').fail(function() {
                    alert_float('danger', 'Failed to record reimbursement');
                }).always(function() {
                    $submitBtn.prop('disabled', false).text(originalText);
                });
            }
        });


        // Clear form when modal is closed
        $('#recordReimbursementModal').on('hidden.bs.modal', function() {
            $('#recordReimbursementForm')[0].reset();
            $('#reimbursementAmount').val(<?php echo abs($final_reimbursement); ?>);
            $('#reimbursementDate').val('<?php echo date('Y-m-d'); ?>');
        });
    });

    function openAddAdvanceModal() {
        $('#addAdvanceModal').modal('show');
    }

    function removeAdvance(advanceId) {
        advanceToRemove = advanceId;
        $('#removeAdvanceModal').modal('show');
    }

    function performRemoveAdvance(advanceId) {
        var confirmBtn = $('#confirmRemoveAdvance');
        var originalText = confirmBtn.text();

        // Show loading state
        confirmBtn.prop('disabled', true).text('Removing...');

        $.post('<?php echo admin_url("expense_reports/remove_advance_from_report"); ?>', {
            report_id: <?php echo $report['id']; ?>,
            advance_id: advanceId
        }, function(response) {
            if (response.success) {
                alert_float('success', 'Advance removed successfully');
                $('#removeAdvanceModal').modal('hide');
                setTimeout(() => {
                    location.reload();
                }, 1000);
            } else {
                alert_float('danger', response.message || 'Failed to remove advance');
            }
        }, 'json').fail(function() {
            alert_float('danger', 'Failed to remove advance');
        }).always(function() {
            confirmBtn.prop('disabled', false).text(originalText);
            advanceToRemove = null;
        });
    }

    function submitReport() {
        if (confirm('Are you sure you want to submit this report? You will not be able to make changes after submission.')) {
            // Submit the report
            $.post('<?php echo admin_url("expense_reports/submit_report/" . $report["id"]); ?>', function(response) {
                if (response.success) {
                    alert_float('success', 'Report submitted successfully');
                    location.reload();
                } else {
                    alert_float('danger', response.message || 'Failed to submit report');
                }
            }, 'json').fail(function() {
                alert_float('danger', 'Failed to submit report');
            });
        }
    }


    function processReport(action, reason = null) {
        // Validate inputs based on action
        if (action === 'reject' && (!reason || reason.trim() === '')) {
            alert_float('warning', 'Please provide a reason for rejection');
            return;
        }

        // Confirmation messages
        const confirmMessages = {
            'approve': 'Are you sure you want to approve this report?',
            'reject': 'Are you sure you want to reject this report?'
        };

        if (!confirm(confirmMessages[action])) {
            return;
        }

        // Prepare data
        const postData = {
            action: action,
            report_id: <?php echo $report['id']; ?>
        };

        // Add reason for rejection
        if (action === 'reject' && reason) {
            postData.rejection_reason = reason.trim();
        }

        // Show loading states
        const buttons = {
            approve: $('#approveBtn'),
            reject: $('#rejectBtn')
        };

        const originalTexts = {
            approve: buttons.approve.text(),
            reject: buttons.reject.text()
        };

        // Disable buttons and show loading
        if (action === 'approve') {
            buttons.approve.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Approving...');
        } else {
            buttons.reject.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Rejecting...');
            // Also disable the modal submit button
            $('#rejectReportForm button[type="submit"]').prop('disabled', true).text('Rejecting...');
        }

        // Single AJAX call for both actions
        $.post('<?php echo admin_url("expense_reports/process_report_action"); ?>', postData, function(response) {
            if (response.success) {
                const successMessages = {
                    'approve': 'Report approved successfully',
                    'reject': 'Report rejected successfully'
                };

                alert_float('success', successMessages[action]);

                // Hide modal if it was a rejection
                if (action === 'reject') {
                    $('#rejectReportModal').modal('hide');
                }

                // Reload page to show updated status
                setTimeout(function() {
                    location.reload();
                }, 1000);

            } else {
                alert_float('danger', response.message || `Failed to ${action} report`);
            }
        }, 'json').fail(function(xhr, status, error) {
            console.error('AJAX Error:', status, error);
            alert_float('danger', `Failed to ${action} report. Please try again.`);
        }).always(function() {
            // Restore button states
            if (action === 'approve') {
                buttons.approve.prop('disabled', false).html(originalTexts.approve);
            } else {
                buttons.reject.prop('disabled', false).html(originalTexts.reject);
                $('#rejectReportForm button[type="submit"]').prop('disabled', false).text('Reject Report');
            }
        });
    }

    // Simplified approve function
    function approveReport() {
        processReport('approve');
    }

    // Modified reject function
    function rejectReport() {
        $('#rejectReportModal').modal('show');
    }

    function openAddExpenseModal() {
        $('#addExpenseModal').modal('show');
    }

    function removeExpense(expenseId) {
        expenseToRemove = expenseId;
        $('#removeExpenseModal').modal('show');
    }

    function performRemoveExpense(expenseId) {
        var confirmBtn = $('#confirmRemoveExpense');
        var originalText = confirmBtn.text();

        confirmBtn.prop('disabled', true).text('Removing...');

        $.post('<?php echo admin_url("expense_reports/remove_expense_from_report"); ?>', {
            report_id: <?php echo $report['id']; ?>,
            expense_id: expenseId
        }, function(response) {
            if (response.success) {
                alert_float('success', 'Expense removed successfully');
                $('#removeExpenseModal').modal('hide');
                setTimeout(() => {
                    location.reload();
                }, 1000);
            } else {
                alert_float('danger', response.message || 'Failed to remove expense');
            }
        }, 'json').fail(function() {
            alert_float('danger', 'Failed to remove expense');
        }).always(function() {
            confirmBtn.prop('disabled', false).text(originalText);
            expenseToRemove = null;
        });
    }

    function openReimbursementModal() {
        $('#recordReimbursementModal').modal('show');
    }


    function undoReimbursement(reimbursementId) {
        $('#undoReimbursementModal').modal('show');

        $('#confirmUndoReimbursement').off('click').on('click', function() {
            var $btn = $(this);
            var originalText = $btn.text();
            $btn.prop('disabled', true).text('Processing...');

            $.post('<?php echo admin_url("expense_reports/undo_reimbursement"); ?>', {
                reimbursement_id: reimbursementId
            }, function(response) {
                if (response.success) {
                    alert_float('success', response.message || 'Reimbursement undone successfully');
                    $('#undoReimbursementModal').modal('hide');
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    alert_float('danger', response.message || 'Failed to undo reimbursement');
                }
            }, 'json').fail(function(xhr, status, error) {
                console.error('AJAX Error:', status, error);
                alert_float('danger', 'Failed to undo reimbursement. Please try again.');
            }).always(function() {
                $btn.prop('disabled', false).text(originalText);
            });
        });
    }

    function generatePDF(type) {
        $('body').append('<div id="loadingOverlay"><div style="display: flex; justify-content: center; align-items: center; margin-top: 30vh; height: 350px; font-size: 18px; color: #888;" id="spinner" class="spinner-container"><div class="dt-loader"><span></span></div></div></div>');
        var PdfName = "<?= expenseReportIdFormat($report['id']) ?>";
        $.ajax({
            url: "<?php echo admin_url('expense_reports/pdf') ?>",
            method: "POST",
            data: {
                report_id: "<?= $report['id'] ?>"
            },
            dataType: 'json'
        }).done(function(result) {
            $('#loadingOverlay').remove();
            if (result.success) {
                const a = document.createElement('a');
                if (type === "download") {
                    a.href = result.pdf_url;
                    a.setAttribute('download', PdfName + '.pdf');
                } else {
                    a.href = result.view_url;
                    a.target = '_blank';
                    a.rel = 'noopener noreferrer';
                }
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
            } else {
                alert_float('danger', result.message);
            }
        });
    }
</script>
</body>

</html>