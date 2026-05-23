<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Expenses_merchants extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('expenses_merchants_model');
    }

    public function index($id = '')
    {
        if (!is_admin()) {
            access_denied('expenses merchants');
        }
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data('expenses_merchants');
        }
        $this->load->view('admin/expense_merchants/manage');
    }

    public function save()
    {
        if (!is_admin()) {
            access_denied('expenses merchants');
        }
        if ($this->input->post()) {
            if (!$this->input->post('id')) {
                $id = $this->expenses_merchants_model->add_merchant($this->input->post());
                echo json_encode([
                    'success' => $id ? true : false,
                    'message' => $id ? "Expense merchant Created successfully" : '',
                    'id'      => $id,
                    'name'    => $this->input->post('name'),
                ]);
            } else {
                $data = $this->input->post();
                $id   = $data['id'];
                unset($data['id']);
                $success = $this->expenses_merchants_model->update_merchant($data, $id);
                $message = "Expense merchant updated successfully";
                echo json_encode(['success' => $success, 'message' => $message]);
            }
        }
    }

    public function delete($id)
    {
        if (!is_admin()) {
            access_denied('expenses');
        }
        if (!$id) {
            redirect(admin_url('expenses_merchants'));
        }
        if (total_rows(db_prefix() . 'expenses', ['merchant_id' => $id]) > 0) {
            set_alert('warning', "Sorry You can't delete this merchant as this merchant having a expenses.");
            redirect(admin_url('expenses_merchants'));
        }
        $response = $this->expenses_merchants_model->delete_merchant($id);
        if ($response) {
            set_alert('success', "Merchant deleted successfully");
        } else {
            set_alert('warning', "Error : Problem deleting merchant.");
        }
        redirect(admin_url('expenses_merchants'));
    }
}
