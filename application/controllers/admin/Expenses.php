<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Expenses extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('expenses_model');
    }

    public function index($id = '')
    {
        if (!has_permission('expenses', '', 'view') && !has_permission('expenses', '', 'view_own')) {
            access_denied();
        }
        if ($this->input->post()) {
            $this->app->get_table_data('expenses');
        }
        $this->load->view('admin/expenses/manage', $data);
    }

    public function expense($id = '')
    {
        if ($this->input->post()) {
            if ($id == '') {
                if (!has_permission('expenses', '', 'create')) {
                    set_alert('danger', _l('access_denied'));
                    echo json_encode([
                        'url' => admin_url('expenses'),
                    ]);
                    die;
                }
                $id = $this->expenses_model->add($this->input->post());
                if ($id) {
                    set_alert('success', _l('added_successfully', _l('expense')));
                    echo json_encode([
                        'url'       => admin_url('expenses'),
                        'expenseid' => $id,
                    ]);
                    die;
                }
                echo json_encode([
                    'url' => admin_url('expenses'),
                ]);
                die;
            }
            if (!has_permission('expenses', '', 'edit')) {
                set_alert('danger', _l('access_denied'));
                echo json_encode([
                    'url' => admin_url('expenses'),
                ]);
                die;
            }
            $success = $this->expenses_model->update($this->input->post(), $id);
            if ($success) {
                set_alert('success', _l('updated_successfully', _l('expense')));
            }
            echo json_encode([
                'url'       => admin_url('expenses'),
                'expenseid' => $id,
            ]);
            die;
        }
        if ($id == '') {
            $title = _l('add_new', _l('expense_lowercase'));
        } else {
            $data['expense'] = $this->expenses_model->get($id);

            if (!$data['expense'] || (!has_permission('expenses', '', 'view') && !has_permission('expenses', '', 'view_own') && $data['expense']->addedfrom != get_staff_user_id())) {
                blank_page(_l('expense_not_found'));
            }

            $title = _l('edit', _l('expense_lowercase'));
            $data['selected_city']    = $this->expenses_model->selected_city($data['expense']->expense_city);
        }

        $data['categories']    = $this->expenses_model->get_category();
        $data['merchants']    = $this->expenses_model->get_merchants();
        $data['reports']    = $this->expenses_model->get_reports();

        $data['bodyclass']  = 'expense';
        $data['title']      = $title;
        $this->load->view('admin/expenses/expense', $data);
    }

    public function delete($id)
    {
        if (!has_permission('expenses', '', 'delete')) {
            access_denied('expenses');
        }
        if (!$id) {
            redirect(admin_url('expenses'));
        }
        $response = $this->expenses_model->delete($id);
        if ($response === true) {
            set_alert('success', _l('deleted', _l('expense')));
        } else {
            if (is_array($response) && $response['invoiced'] == true) {
                set_alert('warning', _l('expense_invoice_delete_not_allowed'));
            } else {
                set_alert('warning', _l('problem_deleting', _l('expense_lowercase')));
            }
        }

        if (strpos($_SERVER['HTTP_REFERER'], 'expenses/') !== false) {
            redirect(admin_url('expenses'));
        } else {
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function categories()
    {
        if (!is_admin()) {
            access_denied('expenses');
        }
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data('expenses_categories');
        }
        $data['title'] = _l('expense_categories');
        $this->load->view('admin/expenses/manage_categories', $data);
    }

    public function category()
    {
        if (!is_admin() && get_option('staff_members_create_inline_expense_categories') == '0') {
            access_denied('expenses');
        }
        if ($this->input->post()) {
            if (!$this->input->post('id')) {
                $id = $this->expenses_model->add_category($this->input->post());
                echo json_encode([
                    'success' => $id ? true : false,
                    'message' => $id ? _l('added_successfully', _l('expense_category')) : '',
                    'id'      => $id,
                    'name'    => $this->input->post('name'),
                ]);
            } else {
                $data = $this->input->post();
                $id   = $data['id'];
                unset($data['id']);
                $success = $this->expenses_model->update_category($data, $id);
                $message = _l('updated_successfully', _l('expense_category'));
                echo json_encode(['success' => $success, 'message' => $message]);
            }
        }
    }

    public function delete_category($id)
    {
        if (!is_admin()) {
            access_denied('expenses');
        }
        if (!$id) {
            redirect(admin_url('expenses/categories'));
        }
        $response = $this->expenses_model->delete_category($id);
        if (is_array($response) && isset($response['referenced'])) {
            set_alert('warning', _l('is_referenced', _l('expense_category_lowercase')));
        } elseif ($response == true) {
            set_alert('success', _l('deleted', _l('expense_category')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('expense_category_lowercase')));
        }
        redirect(admin_url('expenses/categories'));
    }

    public function add_expense_attachment($id)
    {
        handle_expense_attachments($id);
        echo json_encode([
            'url' => admin_url('expenses'),
        ]);
    }

    public function delete_expense_attachment($id, $preview = '')
    {
        $this->db->where('rel_id', $id);
        $this->db->where('rel_type', 'expense');
        $file = $this->db->get(db_prefix() . 'files')->row();

        if ($file->staffid == get_staff_user_id() || is_admin()) {
            $success = $this->expenses_model->delete_expense_attachment($id);
            if ($success) {
                set_alert('success', _l('deleted', _l('expense_receipt')));
            } else {
                set_alert('warning', _l('problem_deleting', _l('expense_receipt_lowercase')));
            }
            if ($preview == '') {
                redirect(admin_url('expenses/expense/' . $id));
            } else {
                redirect(admin_url('expenses'));
            }
        } else {
            access_denied('expenses');
        }
    }

    public function ajax_city_search()
    {
        ini_set('memory_limit', '512M');
        $results = [];
        if ($this->input->post()) {
            $data = $this->input->post();
            if (strlen($data['q']) > 2) {
                $results = $this->expenses_model->search_city($data);
            }
            echo json_encode($results);
            die;
        }
    }

    public function get_expense()
    {
        if (!has_permission('expenses', '', 'view') && !has_permission('expenses', '', 'view_own')) {
            $response = [
                'success' => false,
                'message' => 'Access Denied',
            ];
            echo json_encode($response);
            exit;
        }
        $data = $this->input->post();
        $expense = $this->expenses_model->get($data['id']);
        if (!empty($expense)) {
            $expense->id = expenseIdFormat($expense->id);
            $expense->amount = app_format_money($expense->amount, get_base_currency());
            $expense->report_name = ($expense->report_id) ? expenseReportIdFormat($expense->report_id) . " - " . $expense->report_name : "-";
            $expense->expense_created_at = _d($expense->expense_created_at);
            $expense->date = _d($expense->date);
            if ($expense->status == "Draft") {
                $expense->status == "Reported";
            }
            $response = [
                'success' => true,
                'data' => $expense,
            ];
        } else {
            $response = [
                'success' => false,
                'message' => 'Expense not found',
            ];
        }
        echo json_encode($response);
    }
}
