<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Expense_advance extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('expense_advance_model');
        $this->load->model('expense_trip_model');
        $this->load->model('payment_modes_model');
        $this->load->model('staff_model');
        $this->load->model('expense_reports_model');
    }

    public function index()
    {
        if (!has_permission('expense_advance', '', 'view') && !has_permission('expense_advance', '', 'view_own')) {
            access_denied('expense_advance');
        }
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data('expense_advance');
        }
        $data['staff'] = $this->expense_advance_model->get_staff();
        $data['trips'] = $this->expense_advance_model->get_trips();
        $data['payment_modes'] = $this->payment_modes_model->get('', [], false, true);
        $this->load->view('admin/expense_advance/manage', $data);
    }

    public function save()
    {
        if ($this->input->post()) {
            $data = $this->input->post();
            if ($data['type'] == 'domestic') {
                $data['country'] = null;
                $data['visa_required'] = null;
            }
            if ($data['id'] != '') {
                if (!has_permission('expense_advance', '', 'edit')) {
                    access_denied('expense_advance');
                }
                $oldAdvance = $this->expense_advance_model->get($data['id']);
                if ($oldAdvance['status'] == 'Rejected') {
                    $data['status'] = 'Pending';
                    $data['status_changed_by'] = null;
                    $data['status_updated_at'] = null;
                    $data['reject_reason'] = null;
                }
                $this->expense_advance_model->update($data['id'], $data);
                if ($oldAdvance['status'] == 'Rejected' && $data['status'] == 'Pending') {
                    $notifiedUsers = [];
                    if (!is_admin()) {
                        $admins = $this->staff_model->get_admins();
                        foreach ($admins as $member) {
                            if ($member['staffid'] != 0) {
                                $notified = add_notification([
                                    'description' => 'not_expense_advance_pay_request',
                                    'touserid' => $member['staffid'],
                                    'fromcompany' => 1,
                                    'fromuserid' => null,
                                    'additional_data' => serialize([
                                        expenseAdvancePaymentIdFormat($data['id']),
                                        get_staff_full_name(),

                                    ]),
                                    'link' => 'expense_advance',
                                ]);
                                if ($notified) {
                                    array_push($notifiedUsers, $member['staffid']);
                                }
                            }
                        }
                        pusher_trigger_notification($notifiedUsers);
                    }
                }
                set_alert('success', 'Record updated successfully.');
            } else {
                if (!has_permission('expense_advance', '', 'create')) {
                    access_denied('expense_advance');
                }
                $id = $this->expense_advance_model->add($data);
                if ($id) {
                    $notifiedUsers = [];
                    if (!is_admin()) {
                        $admins = $this->staff_model->get_admins();
                        foreach ($admins as $member) {
                            if ($member['staffid'] != 0) {
                                $notified = add_notification([
                                    'description' => 'not_expense_advance_pay_request',
                                    'touserid' => $member['staffid'],
                                    'fromcompany' => 1,
                                    'fromuserid' => null,
                                    'additional_data' => serialize([
                                        expenseAdvancePaymentIdFormat($id),
                                        get_staff_full_name(),

                                    ]),
                                    'link' => 'expense_advance',
                                ]);
                                if ($notified) {
                                    array_push($notifiedUsers, $member['staffid']);
                                }
                            }
                        }
                        pusher_trigger_notification($notifiedUsers);
                    }
                    set_alert('success', 'Record Created successfully.');
                } else {
                    set_alert('warning', 'Error adding Advance Payment.');
                }
            }
        }
        redirect(admin_url('expense_advance'));
    }

    public function delete($id)
    {
        if (!has_permission('expense_advance', '', 'delete')) {
            access_denied('expense_advance');
        }
        if (!$id) {
            access_denied('expense_advance');
        }
        $response = $this->expense_advance_model->delete($id);
        if ($response) {
            set_alert('success', 'Advance Payment deleted successfully.');
        } else {
            set_alert('warning', 'Error deleting Advance Payment Record.');
        }
        redirect(admin_url('expense_advance'));
    }

    public function get_expense_advance()
    {
        $id = $this->input->post('id');
        if ($id) {
            $expense_advance = $this->expense_advance_model->get($id);
            if ($expense_advance) {
                $expense_advance['trip_name'] = '-';
                $trip = $this->expense_trip_model->get($expense_advance['trip']);
                if ($trip) {
                    $expense_advance['trip_name'] = expenseTripIdFormat($trip['id']) . " - " . $trip['name'];
                }
                $expense_advance['report_name'] = '-';
                $report = $this->expense_reports_model->get($expense_advance['report_id']);
                if ($report) {
                    $expense_advance['report_name'] = expenseTripIdFormat($report['id']) . " - " . $report['report_name'];
                }
                $expense_advance['pay_id'] = expenseAdvancePaymentIdFormat($expense_advance['id']);
                $expense_advance['date'] = _d($expense_advance['date']);
                $expense_advance['staff_name'] = get_staff_full_name($expense_advance['staff_id']);
                $expense_advance['payment_mode_name'] = $this->payment_modes_model->get($expense_advance['payment_mode'])->name;
                $expense_advance['reference'] = $expense_advance['reference'] ?? '';
                $expense_advance['notes'] = $expense_advance['notes'] ?? '';
                echo json_encode(['success' => true, 'data' => $expense_advance]);
            } else {
                echo json_encode(['success' => false, 'message' => 'No expense trips found for this customer.']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid Record ID.']);
        }
    }

    public function status_change()
    {
        if (!has_permission('expense_advance', '', 'approve_reject_payment')) {
            echo json_encode(['success' => false, 'message' => 'You do not have permission to approve/reject expense advances.']);
            return;
        }

        if ($this->input->post()) {

            $id = $this->input->post('id');
            $status = $this->input->post('status');
            $reason = $this->input->post('reason');
            $getData = $this->expense_advance_model->get($id);
            if (!$getData) {
                echo json_encode(['success' => false, 'message' => 'Invalid Record ID.']);
                return;
            }
            if (get_staff_user_id() == $getData['staff_id']) {
                echo json_encode(['success' => false, 'message' => 'You cannot approve or reject your own expense advance request.']);
                return;
            }

            $data = [];
            $data['status'] = $status;
            $data['status_changed_by'] = get_staff_user_id();
            $data['status_updated_at'] = date('Y-m-d H:i:s');
            $data['reject_reason'] = ($status == 'Rejected') ? $reason : null;
            $this->expense_advance_model->update($id, $data, true);
            add_notification([
                'description' => ($status == 'Approved') ? 'not_expense_advance_pay_request_approved' : 'not_expense_advance_pay_request_rejected',
                'touserid' => $getData['staff_id'],
                'fromcompany' => 1,
                'fromuserid' => null,
                'additional_data' => serialize([
                    expenseAdvancePaymentIdFormat($id),
                    get_staff_full_name(),
                ]),
                'link' => 'expense_advance',
            ]);
            pusher_trigger_notification([$getData['staff_id']]);
            if ($status == 'Rejected') {
                echo json_encode(['success' => true, 'message' => "Advance Payment rejected successfully."]);
            } else {
                echo json_encode(['success' => true, 'message' => "Advance Payment approved successfully."]);
            }
        }
    }

    public function bulk_status_change()
    {
        if (!has_permission('expense_advance', '', 'approve_reject_payment')) {
            echo json_encode(['success' => false, 'message' => 'You do not have permission to approve/reject expense advances.']);
            return;
        }

        if ($this->input->post()) {
            $total_changes = 0;
            $ids = $this->input->post('id');
            $status = $this->input->post('status');
            $reason = $this->input->post('reason');
            if (is_array($ids)) {
                foreach ($ids as $id) {
                    $getData = $this->expense_advance_model->get($id);
                    if (get_staff_user_id() == $getData['staff_id']) {
                        continue;
                    }
                    $data = [];
                    $data['status'] = $status;
                    $data['status_changed_by'] = get_staff_user_id();
                    $data['status_updated_at'] = date('Y-m-d H:i:s');
                    $data['reject_reason'] = ($status == 'Rejected') ? $reason : null;
                    $this->expense_advance_model->update($id, $data, true);
                    add_notification([
                        'description' => ($status == 'Approved') ? 'not_expense_advance_pay_request_approved' : 'not_expense_advance_pay_request_rejected',
                        'touserid' => $getData['staff_id'],
                        'fromcompany' => 1,
                        'fromuserid' => null,
                        'additional_data' => serialize([
                            expenseAdvancePaymentIdFormat($id),
                            get_staff_full_name(),
                        ]),
                        'link' => 'expense_advance',
                    ]);
                    pusher_trigger_notification([$getData['staff_id']]);
                    $total_changes++;
                }
            }
            if ($total_changes > 0) {
                echo json_encode(['success' => true, 'message' => "Advance Payments updated successfully."]);
            } else {
                echo json_encode(['success' => false, 'message' => "Error : No Advance Payments were updated."]);
            }
        }
    }
}
