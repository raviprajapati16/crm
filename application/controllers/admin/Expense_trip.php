<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Expense_trip extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('expense_trip_model');
    }
    public function index()
    {
        if (!has_permission('expense_trip', '', 'view') && !has_permission('expense_trip', '', 'view_own')) {
            access_denied('expense_trip');
        }
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data('expense_trip');
        }
        $this->load->view('admin/expense_trip/manage');
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
                if (!has_permission('expense_trip', '', 'edit')) {
                    access_denied('expense_trip');
                }
                $this->expense_trip_model->update($data['id'], $data);
                set_alert('success', 'Trip updated successfully.');
            } else {
                if (!has_permission('expense_trip', '', 'create')) {
                    access_denied('expense_trip');
                }
                $id = $this->expense_trip_model->add($data);
                if ($id) {
                    set_alert('success', 'Trip added successfully.');
                } else {
                    set_alert('warning', 'Error adding Trip.');
                }
            }
        }
        redirect(admin_url('expense_trip'));
    }

    public function delete($id)
    {
        if (!has_permission('expense_trip', '', 'delete')) {
            access_denied('expense_trip');
        }
        if (!$id) {
            access_denied('expense_trip');
        }

        $check = $this->db->get_where(db_prefix() . 'expense_advance', ['trip' => $id]);
        if ($check->num_rows() > 0) {
            set_alert('warning', 'This trip is associated with expense advances.');
            redirect(admin_url('expense_trip'));
        }

        $response = $this->expense_trip_model->delete($id);
        if ($response) {
            set_alert('success', 'Trip deleted successfully.');
        } else {
            set_alert('warning', 'Error deleting Trip.');
        }
        redirect(admin_url('expense_trip'));
    }

    public function get_expense_trip()
    {
        if (!has_permission('expense_trip', '', 'edit')) {
            echo json_encode(['success' => false, 'message' => 'You do not have permission to edit expense trips.']);
            return;
        }
        $id = $this->input->post('id');
        if ($id) {
            $expense_trip = $this->expense_trip_model->get($id);
            if ($expense_trip) {
                echo json_encode(['success' => true, 'data' => $expense_trip]);
            } else {
                echo json_encode(['success' => false, 'message' => 'No expense trips found for this customer.']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid Record ID.']);
        }
    }
}
