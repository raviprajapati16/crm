<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Expense_reports extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('expense_advance_model');
        $this->load->model('expense_trip_model');
        $this->load->model('payment_modes_model');
        $this->load->model('staff_model');
        $this->load->model('expense_reports_model');
        $this->load->model('expenses_model');
        $this->load->model('expense_reimbursement_model');
    }
    public function index()
    {
        if (!has_permission('expense_reports', '', 'view') && !has_permission('expense_reports', '', 'view_own')) {
            access_denied('expense_reports');
        }
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data('expense_reports');
        }
        $data['trips'] = $this->expense_reports_model->get_trips();
        $this->load->view('admin/expense_reports/manage', $data);
    }

    public function save()
    {
        if ($this->input->post()) {
            $data = $this->input->post();
            if ($data['id'] != '') {
                if (!has_permission('expense_reports', '', 'edit')) {
                    access_denied('expense_reports');
                }
                $this->expense_reports_model->update($data['id'], $data);
                set_alert('success', 'Report updated successfully.');
            } else {
                if (!has_permission('expense_reports', '', 'create')) {
                    access_denied('expense_reports');
                }
                $id = $this->expense_reports_model->add($data);
                if ($id) {
                    set_alert('success', 'Report Created successfully.');
                } else {
                    set_alert('warning', 'Error adding Expense Report.');
                }
            }
        }
        redirect(admin_url('expense_reports'));
    }

    public function delete($id)
    {
        if (!has_permission('expense_reports', '', 'delete')) {
            access_denied('expense_reports');
        }
        if (!$id) {
            access_denied('expense_reports');
        }
        $response = $this->expense_reports_model->delete($id);
        if ($response) {
            set_alert('success', 'Expense Report deleted successfully.');
        } else {
            set_alert('warning', 'Error deleting Expense Report.');
        }
        redirect(admin_url('expense_reports'));
    }

    public function get_expense_reports()
    {
        $id = $this->input->post('id');
        if ($id) {
            $expense_reports = $this->expense_reports_model->get($id);
            if ($expense_reports) {
                $expense_reports['start_date'] = _d($expense_reports['start_date']);
                $expense_reports['end_date'] = _d($expense_reports['end_date']);
                echo json_encode(['success' => true, 'data' => $expense_reports]);
            } else {
                echo json_encode(['success' => false, 'message' => 'No expense reports found for this customer.']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid Record ID.']);
        }
    }

    public function report($id)
    {
        if (!has_permission('expense_reports', '', 'view') && !has_permission('expense_reports', '', 'view_own')) {
            access_denied('expense_reports');
        }

        $data['report'] = $this->expense_reports_model->get($id);
        if (empty($data['report'])) {
            access_denied('expense_reports');
            exit;
        }
        if (!has_permission('expense_reports', '', 'view') && $data['report']['created_by'] != get_staff_user_id()) {
            access_denied('expense_reports');
            exit;
        }
        $data['unreported_advances'] = $this->expense_reports_model->get_expense_unreported_advances();
        $data['unreported_expenses'] = $this->expense_reports_model->get_expense_unreported_expenses();
        $data['expense_advances'] = $this->expense_reports_model->get_expense_advances_by_report($id);
        $data['trip'] = $this->expense_reports_model->get_expense_trips_by_report($data['report']['trip_id']);
        $data['expenses'] = $this->expense_reports_model->get_expenses_by_report($id);
        if (!empty($data['expenses'])) {
            foreach ($data['expenses'] as $key => $expense) {
                $attachment = $this->expenses_model->get_expense_attachment($expense['id']);
                if (!empty($attachment)) {
                    $data['expenses'][$key]['attachment'] = site_url('download/file/expense/' . $expense['id']);
                }
            }
        }
        $data['payment_modes'] = $this->payment_modes_model->get('', [], false, true);
        $data['reimbursement'] = $this->expense_reimbursement_model->get_reimbursement_by_report($id);
        $this->load->view('admin/expense_reports/report', $data);
    }

    public function add_expenses_to_report()
    {
        if ($this->input->post()) {
            $report_id = $this->input->post('report_id');
            $expense_ids = $this->input->post('expense_ids');
            if (!has_permission('expense_reports', '', 'view') && !has_permission('expense_reports', '', 'view_own')) {
                echo json_encode(['success' => false, 'message' => 'Access Denied']);
                exit;
            }

            $report = $this->expense_reports_model->get($report_id);
            if (empty($report)) {
                echo json_encode(['success' => false, 'message' => 'Invalid Report ID']);
                exit;
            }
            if (!has_permission('expense_reports', '', 'view') && $report['created_by'] != get_staff_user_id()) {
                echo json_encode(['success' => false, 'message' => 'Access Denied']);
                exit;
            }

            if (empty($report_id) || empty($expense_ids) || !is_array($expense_ids)) {
                echo json_encode(['success' => false, 'message' => 'Invalid data provided']);
                return;
            }

            if ($report['status'] != 'Draft' && $report['status'] != 'Rejected') {
                echo json_encode(['success' => false, 'message' => 'Cannot modify submitted report']);
                return;
            }

            try {
                foreach ($expense_ids as $expense_id) {
                    $this->expenses_model->update_manually($expense_id, ['report_id' => $report_id]);
                    log_activity('Expense Report : Expense Added to Report ' . $report_id . ' Expense ID : ' . $expense_id);
                }
                echo json_encode([
                    'success' => true,
                    'message' => count($expense_ids) . ' expenses(s) added successfully'
                ]);
            } catch (Exception $e) {

                echo json_encode([
                    'success' => false,
                    'message' => 'Error: ' . $e->getMessage()
                ]);
            }
        }
    }

    public function remove_expense_from_report()
    {

        if ($this->input->post()) {
            $report_id = $this->input->post('report_id');
            $expense_id = $this->input->post('expense_id');

            if (!has_permission('expense_reports', '', 'view') && !has_permission('expense_reports', '', 'view_own')) {
                echo json_encode(['success' => false, 'message' => 'Access Denied']);
                exit;
            }

            $report = $this->expense_reports_model->get($report_id);
            if (empty($report)) {
                echo json_encode(['success' => false, 'message' => 'Invalid Report ID']);
                exit;
            }
            if (!has_permission('expense_reports', '', 'view') && $report['created_by'] != get_staff_user_id()) {
                echo json_encode(['success' => false, 'message' => 'Access Denied']);
                exit;
            }

            if ($report['status'] != 'Draft' && $report['status'] != 'Rejected') {
                echo json_encode(['success' => false, 'message' => 'Cannot remove Expense from submitted reports']);
                return;
            }
            $result =  $this->expenses_model->update_manually($expense_id, ['report_id' => null]);
            if ($result) {
                log_activity('Expense Report : Expense Removed from Report ID :' . $report_id . ' Expense ID : ' . $expense_id);
                echo json_encode(['success' => true, 'message' => 'Expense removed successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to remove Expense']);
            }
        }
    }

    public function add_advances_to_report()
    {
        if ($this->input->post()) {
            $report_id = $this->input->post('report_id');
            $advance_ids = $this->input->post('advance_ids');
            if (!has_permission('expense_reports', '', 'view') && !has_permission('expense_reports', '', 'view_own')) {
                echo json_encode(['success' => false, 'message' => 'Access Denied']);
                exit;
            }

            $report = $this->expense_reports_model->get($report_id);
            if (empty($report)) {
                echo json_encode(['success' => false, 'message' => 'Invalid Report ID']);
                exit;
            }
            if (!has_permission('expense_reports', '', 'view') && $report['created_by'] != get_staff_user_id()) {
                echo json_encode(['success' => false, 'message' => 'Access Denied']);
                exit;
            }

            if (empty($report_id) || empty($advance_ids) || !is_array($advance_ids)) {
                echo json_encode(['success' => false, 'message' => 'Invalid data provided']);
                return;
            }

            if ($report['status'] != 'Draft' && $report['status'] != 'Rejected') {
                echo json_encode(['success' => false, 'message' => 'Cannot modify submitted report']);
                return;
            }

            try {
                foreach ($advance_ids as $advance_id) {
                    $this->expense_advance_model->update($advance_id, ['report_id' => $report_id]);
                    log_activity('Expense Report : Advance Payment Added to Report ID : ' . $report_id . ' Advance Payment ID : ' . $advance_id);
                }
                echo json_encode([
                    'success' => true,
                    'message' => count($advance_ids) . ' advance payment(s) added successfully'
                ]);
            } catch (Exception $e) {

                echo json_encode([
                    'success' => false,
                    'message' => 'Error: ' . $e->getMessage()
                ]);
            }
        }
    }

    public function remove_advance_from_report()
    {

        if ($this->input->post()) {
            $report_id = $this->input->post('report_id');
            $advance_id = $this->input->post('advance_id');

            if (!has_permission('expense_reports', '', 'view') && !has_permission('expense_reports', '', 'view_own')) {
                echo json_encode(['success' => false, 'message' => 'Access Denied']);
                exit;
            }

            $report = $this->expense_reports_model->get($report_id);
            if (empty($report)) {
                echo json_encode(['success' => false, 'message' => 'Invalid Report ID']);
                exit;
            }
            if (!has_permission('expense_reports', '', 'view') && $report['created_by'] != get_staff_user_id()) {
                echo json_encode(['success' => false, 'message' => 'Access Denied']);
                exit;
            }

            if ($report['status'] != 'Draft' && $report['status'] != 'Rejected') {
                echo json_encode(['success' => false, 'message' => 'Cannot remove advances from submitted reports']);
                return;
            }
            $result =  $this->expense_advance_model->update($advance_id, ['report_id' => null]);
            if ($result) {
                log_activity('Expense Report : Advance Payment Removed from Report ID : ' . $report_id . ' Advance Payment ID : ' . $advance_id);
                echo json_encode(['success' => true, 'message' => 'Advance removed successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to remove advance']);
            }
        }
    }

    public function submit_report($id)
    {

        if (!has_permission('expense_reports', '', 'view') && !has_permission('expense_reports', '', 'view_own')) {
            echo json_encode(['success' => false, 'message' => 'Access Denied']);
            exit;
        }

        $report = $this->expense_reports_model->get($id);
        if (empty($report)) {
            echo json_encode(['success' => false, 'message' => 'Invalid Report ID']);
            exit;
        }
        if (!has_permission('expense_reports', '', 'view') && $report['created_by'] != get_staff_user_id()) {
            echo json_encode(['success' => false, 'message' => 'Access Denied']);
            exit;
        }

        $check = $this->expense_reports_model->update($id, [
            'status' => 'Awaiting Approval',
            'status_updated_by' => get_staff_user_id(),
            'status_updated_at' => date('Y-m-d H:i:s'),
            'rejection_reason' => null
        ], false);
        if (!$check) {
            echo json_encode(['success' => false, 'message' => 'Failed to submit report']);
            exit;
        }

        if (!is_admin()) {
            $notifiedUsers = [];
            $admins = $this->staff_model->get_admins();
            foreach ($admins as $member) {
                if ($member['staffid'] != 0) {
                    $notified = add_notification([
                        'description' => 'not_expense_report_request',
                        'touserid' => $member['staffid'],
                        'fromcompany' => 1,
                        'fromuserid' => null,
                        'additional_data' => serialize([
                            expenseReportIdFormat($id),
                            get_staff_full_name(),

                        ]),
                        'link' => 'expense_reports/report/' . $id,
                    ]);
                    if ($notified) {
                        array_push($notifiedUsers, $member['staffid']);
                    }
                }
            }
            pusher_trigger_notification($notifiedUsers);
        }

        log_activity('Expense Report : Report ' . $id . ' submitted for approval');
        echo json_encode(['success' => true, 'message' => 'Report submitted successfully']);
        exit;
    }


    public function process_report_action()
    {
        if (!$this->input->post()) {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            return;
        }

        $action = $this->input->post('action');
        $report_id = $this->input->post('report_id');

        if (!in_array($action, ['approve', 'reject'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            return;
        }

        if (!$report_id || !is_numeric($report_id)) {
            echo json_encode(['success' => false, 'message' => 'Invalid report ID']);
            return;
        }

        if (!is_admin() && !has_permission('expense_reports', '', 'approve_reject_report')) {
            echo json_encode(['success' => false, 'message' => 'You do not have permission to perform this action']);
            return;
        }

        $report = $this->expense_reports_model->get($report_id);
        if (!$report) {
            echo json_encode(['success' => false, 'message' => 'Report not found']);
            return;
        }

        if ($report['status'] != 'Awaiting Approval') {
            echo json_encode(['success' => false, 'message' => 'Report is not awaiting approval']);
            return;
        }

        try {
            $result = false;
            $message = '';

            if ($action === 'approve') {
                $update_data = [
                    'status' => 'Approved',
                    'status_updated_by' => get_staff_user_id(),
                    'status_updated_at' => date('Y-m-d H:i:s')
                ];

                $result = $this->expense_reports_model->update($report_id, $update_data);
                $message = 'Report approved successfully';
                log_activity('Expense Report : Report ' . $report_id . ' approved by ' . get_staff_full_name());
            } elseif ($action === 'reject') {
                $rejection_reason = $this->input->post('rejection_reason');

                if (empty(trim($rejection_reason))) {
                    echo json_encode(['success' => false, 'message' => 'Rejection reason is required']);
                    return;
                }

                $update_data = [
                    'status' => 'Rejected',
                    'status_updated_by' => get_staff_user_id(),
                    'status_updated_at' => date('Y-m-d H:i:s'),
                    'rejection_reason' => trim($rejection_reason)
                ];

                $result = $this->expense_reports_model->update($report_id, $update_data);
                $message = 'Report rejected successfully';
                log_activity('Expense Report : Report ' . $report_id . ' rejected by ' . get_staff_full_name());
            }

            if ($result) {
                if ($report['created_by'] != get_staff_user_id()) {
                    add_notification([
                        'description' => ($action == 'approve') ? 'not_expense_report_approved' : 'not_expense_report_rejected',
                        'touserid' => $report['created_by'],
                        'fromcompany' => 1,
                        'fromuserid' => null,
                        'additional_data' => serialize([
                            expenseReportIdFormat($report_id),
                            get_staff_full_name(),
                        ]),
                        'link' => 'expense_advance',
                    ]);
                    pusher_trigger_notification([$report['created_by']]);
                }
                echo json_encode([
                    'success' => true,
                    'message' => $message,
                    'action' => $action,
                    'report_id' => $report_id
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update report status']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'An error occurred while processing the request']);
        }
    }

    public function undo_reimbursement()
    {
        if (!has_permission('expense_reports', '', 'approve_reject_report') && !is_admin()) {
            echo json_encode(['success' => false, 'message' => 'Access Denied']);
            return;
        }

        $reimbursement_id = $this->input->post('reimbursement_id');
        if (!$reimbursement_id) {
            echo json_encode(['success' => false, 'message' => 'Invalid reimbursement ID']);
            return;
        }

        $reimbursement = $this->expense_reimbursement_model->get_reimbursement($reimbursement_id);
        if (!$reimbursement) {
            echo json_encode(['success' => false, 'message' => 'Reimbursement not found']);
            return;
        }

        $this->expense_reports_model->update($reimbursement['report_id'], [
            'status' => 'Approved',
            'status_updated_by' => get_staff_user_id(),
            'status_updated_at' => date('Y-m-d H:i:s'),
        ]);

        $success = $this->expense_reimbursement_model->delete_reimbursement($reimbursement_id);

        if ($success) {
            log_activity('Expense Report : Reimbursement ID ' . $reimbursement_id . ' undone for Report ID: ' . $reimbursement['report_id']);
            echo json_encode(['success' => true, 'message' => 'Reimbursement undone successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to undo reimbursement']);
        }
    }

    public function record_reimbursement()
    {
        if ($this->input->post()) {
            $data = $this->input->post();

            if (!has_permission('expense_reports', '', 'approve_reject_report') && !is_admin()) {
                echo json_encode(['success' => false, 'message' => 'Access Denied']);
                return;
            }

            $report = $this->expense_reports_model->get($data['report_id']);
            if (empty($report)) {
                echo json_encode(['success' => false, 'message' => 'Invalid Report ID']);
                exit;
            }

            if ($report['status'] != 'Approved') {
                echo json_encode(['success' => false, 'message' => 'Cannot record reimbursement for unapproved report']);
                return;
            }

            try {
                $required_fields = ['amount', 'date'];

                if ($data['type'] == "refunded") {
                    array_push($required_fields, 'paid_through');
                }

                foreach ($required_fields as $field) {
                    if (empty($data[$field])) {
                        echo json_encode(['success' => false, 'message' => 'Missing required field: ' . str_replace('_', ' ', $field)]);
                        return;
                    }
                }

                if (empty($data['amount']) || $data['amount'] == 0) {
                    echo json_encode(['success' => false, 'message' => 'Invalid amount']);
                    return;
                }

                $reimbursement_data = [
                    'report_id' => $data['report_id'],
                    'amount' => $data['amount'],
                    'date' => to_sql_date($data['date']),
                    'type' => $data['type'],
                    'reference' => isset($data['reference']) ? $data['reference'] : null,
                    'notes' => isset($data['notes']) ? $data['notes'] : null,
                    'paid_through' => isset($data['paid_through']) ? $data['paid_through'] : null,
                    'created_by' => get_staff_user_id(),
                    'created_at' => date('Y-m-d H:i:s')
                ];

                $reimbursement_id = $this->expense_reimbursement_model->add_reimbursement($reimbursement_data);
                if ($reimbursement_id) {
                    $update_data = [
                        'status' => 'Reimbursed',
                        'status_updated_by' => get_staff_user_id(),
                        'status_updated_at' => date('Y-m-d H:i:s')
                    ];

                    $this->expense_reports_model->update($data['report_id'], $update_data, false);

                    if (get_staff_user_id() != $report['created_by']) {
                        add_notification([
                            'description' => 'not_expense_report_reimbursed',
                            'touserid' => $report['created_by'],
                            'fromcompany' => 1,
                            'fromuserid' => null,
                            'additional_data' => serialize([
                                expenseReportIdFormat($data['report_id']),
                                get_staff_full_name(),
                            ]),
                            'link' => 'expense_reports/report/' . $data['report_id'],
                        ]);
                        pusher_trigger_notification([$report['created_by']]);
                    }

                    echo json_encode([
                        'success' => true,
                        'message' => "Reimbursement recorded successfully",
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Failed to record reimbursement'
                    ]);
                }
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Error: ' . $e->getMessage()
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid request method'
            ]);
        }
    }

    public function pdf()
    {
        if (!has_permission('goals_dashboard', '', 'view') && !has_permission('goals_dashboard', '', 'view_own')) {
            echo json_encode([
                'success' => false,
                'message' => 'Access Denied'
            ]);
        }
        try {
            $data = $this->input->post();
            $report = $this->expense_reports_model->get($data['report_id']);
            $report['expense_advances'] = $this->expense_reports_model->get_expense_advances_by_report($data['report_id']);
            $report['trip_data'] = $this->expense_reports_model->get_expense_trips_by_report($report['trip_id']);
            $report['expenses_data'] = $this->expense_reports_model->get_expenses_by_report($data['report_id']);

            foreach ($report['expenses_data'] as $key => $expense) {
                $attachment = $this->expense_reports_model->get_expenses_attachment($expense['id']);
                if (!empty($attachment)) {
                    $filePath = FCPATH . 'uploads/expenses/' . $expense['id'] . '/' . $attachment->file_name;
                    $fileExt = pathinfo($filePath, PATHINFO_EXTENSION);

                    if (strtolower($fileExt) == 'pdf') {
                        $receipts = [];
                        try {
                            $imagick = new \Imagick();
                            $imagick->setResolution(150, 150);
                            $imagick->readImage($filePath);
                            $imagick->setImageFormat('jpg');

                            $numPages = $imagick->getNumberImages();
                            for ($i = 0; $i < $numPages; $i++) {
                                $imagick->setIteratorIndex($i);
                                $page = $imagick->getImage();
                                $page->setImageBackgroundColor('white');
                                $page = $page->flattenImages();

                                $outputPath = 'uploads/temp_mail_attachments/receipt_page_' . $expense['id'] . '_' . $i . '_' . time() . '.jpg';
                                $page->writeImage(FCPATH . $outputPath);

                                $receipts[] = site_url($outputPath);

                                $page->clear();
                                $page->destroy();
                            }

                            $imagick->clear();
                            $imagick->destroy();
                        } catch (Exception $e) {

                        }

                        $report['expenses_data'][$key]['receipts'] = $receipts;
                    } else {
                        $report['expenses_data'][$key]['receipts'][] = site_url('uploads/expenses/' . $expense['id'] . '/' . $attachment->file_name);
                    }
                }
            }
            $pdf = expense_report_mpdf($report);
            $tempPdfPath = 'uploads/temp_mail_attachments/' . expenseReportIdFormat($data['report_id']) . '.pdf';
            $pdf->Output($tempPdfPath, \Mpdf\Output\Destination::FILE);
            //$pdf->Output('ffff.pdf', 'I');
            $response['success'] = true;
            $response['pdf_url'] = site_url($tempPdfPath);
            $response['view_url'] = site_url('download/file_download?path=' . $tempPdfPath);
        } catch (Exception $e) {
            $response['success'] = false;
            $response['message'] = $e->getMessage();
        }
        echo json_encode($response);
    }
}
