<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Expenses_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get($id = '', $where = [])
    {
        $this->db->select('*,' . db_prefix() . 'expenses.id as id,' . db_prefix() . 'expenses_categories.name as category_name, ' . db_prefix() . 'expenses.id as expenseid,' . db_prefix() . 'expenses.created_by as created_by,' . db_prefix() . 'expense_merchant.name as merchant_name,' . db_prefix() . 'expense_reports.report_name as report_name,' . db_prefix() . 'expense_reports.status as status,CONCAT(t_submitter.firstname, " ", t_submitter.lastname) as created_by_name,' . db_prefix() . 'expense_reports.id as report_id,CONCAT(t_city.city, ", ", t_city.country) as city_name,' . db_prefix() . 'expenses.created_at as expense_created_at');
        $this->db->from(db_prefix() . 'expenses');
        $this->db->join(db_prefix() . 'expenses_categories', '' . db_prefix() . 'expenses_categories.id = ' . db_prefix() . 'expenses.category');
        $this->db->join(db_prefix() . 'expense_merchant', '' . db_prefix() . 'expense_merchant.id = ' . db_prefix() . 'expenses.merchant_id', 'left');
        $this->db->join(db_prefix() . 'expense_reports', '' . db_prefix() . 'expense_reports.id = ' . db_prefix() . 'expenses.report_id', 'left');
        $this->db->join(db_prefix() . 'staff t_submitter', 't_submitter.staffid = ' . db_prefix() . 'expenses.created_by', 'left');
        $this->db->join(db_prefix() . 'cities t_city', 't_city.id = ' . db_prefix() . 'expenses.expense_city', 'left');
        $this->db->where($where);

        if (is_numeric($id)) {
            $this->db->where(db_prefix() . 'expenses.id', $id);
            $expense = $this->db->get()->row();
            if ($expense) {
                $expense->attachment            = '';
                $expense->filetype              = '';
                $expense->attachment_added_from = 0;

                $this->db->where('rel_id', $id);
                $this->db->where('rel_type', 'expense');
                $file = $this->db->get(db_prefix() . 'files')->row();

                if ($file) {
                    $expense->attachment            = $file->file_name;
                    $expense->filetype              = $file->filetype;
                    $expense->attachment_added_from = $file->staffid;
                    $expense->attachment_url =  site_url('download/file/expense/' . $expense->id);
                }
            }

            return $expense;
        }
        $this->db->order_by('date', 'desc');

        $query = $this->db->get();
        return $query->result_array();
    }

    /**
     * Add new expense
     * @param mixed $data All $_POST data
     * @return  mixed
     */
    public function add($data)
    {
        $data['date'] = to_sql_date($data['date']);
        $data['note'] = nl2br($data['note']);
        if (isset($data['billable'])) {
            $data['billable'] = 1;
        } else {
            $data['billable'] = 0;
        }

        if (isset($data['reimbursement'])) {
            $data['reimbursement'] = 1;
        } else {
            $data['reimbursement'] = 0;
        }

        $data['created_by'] = get_staff_user_id();
        $data['created_at'] = date('Y-m-d H:i:s');
        $this->db->insert(db_prefix() . 'expenses', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            log_activity('New Expense Added [' . $insert_id . ']');
            return $insert_id;
        }
        return false;
    }

    public function get_child_expenses($id)
    {
        $this->db->select('id');
        $this->db->where('recurring_from', $id);
        $expenses = $this->db->get(db_prefix() . 'expenses')->result_array();

        $_expenses = [];
        foreach ($expenses as $expense) {
            $_expenses[] = $this->get($expense['id']);
        }

        return $_expenses;
    }

    public function get_expenses_total($data)
    {
        $this->load->model('currencies_model');
        $base_currency     = $this->currencies_model->get_base_currency()->id;
        $base              = true;
        $currency_switcher = false;
        if (isset($data['currency'])) {
            $currencyid        = $data['currency'];
            $currency_switcher = true;
        } elseif (isset($data['customer_id']) && $data['customer_id'] != '') {
            $currencyid = $this->clients_model->get_customer_default_currency($data['customer_id']);
            if ($currencyid == 0) {
                $currencyid = $base_currency;
            } else {
                if (total_rows(db_prefix() . 'expenses', [
                    'currency' => $base_currency,
                    'clientid' => $data['customer_id'],
                ])) {
                    $currency_switcher = true;
                }
            }
        } elseif (isset($data['project_id']) && $data['project_id'] != '') {
            $this->load->model('projects_model');
            $currencyid = $this->projects_model->get_currency($data['project_id'])->id;
        } else {
            $currencyid = $base_currency;
            if (total_rows(db_prefix() . 'expenses', [
                'currency !=' => $base_currency,
            ])) {
                $currency_switcher = true;
            }
        }

        $currency = get_currency($currencyid);

        $has_permission_view = has_permission('expenses', '', 'view');
        $_result             = [];

        for ($i = 1; $i <= 5; $i++) {
            $this->db->select('amount,tax,tax2,invoiceid');
            $this->db->where('currency', $currencyid);

            if (isset($data['years']) && count($data['years']) > 0) {
                $this->db->where('YEAR(date) IN (' . implode(', ', $data['years']) . ')');
            } else {
                $this->db->where('YEAR(date) = ' . date('Y'));
            }
            if (isset($data['customer_id']) && $data['customer_id'] != '') {
                $this->db->where('clientid', $data['customer_id']);
            }
            if (isset($data['project_id']) && $data['project_id'] != '') {
                $this->db->where('project_id', $data['project_id']);
            }

            if (!$has_permission_view) {
                $this->db->where('addedfrom', get_staff_user_id());
            }
            switch ($i) {
                case 1:
                    $key = 'all';

                    break;
                case 2:
                    $key = 'billable';
                    $this->db->where('billable', 1);

                    break;
                case 3:
                    $key = 'non_billable';
                    $this->db->where('billable', 0);

                    break;
                case 4:
                    $key = 'billed';
                    $this->db->where('billable', 1);
                    $this->db->where('invoiceid IS NOT NULL');
                    $this->db->where('invoiceid IN (SELECT invoiceid FROM ' . db_prefix() . 'invoices WHERE status=2 AND id=' . db_prefix() . 'expenses.invoiceid)');

                    break;
                case 5:
                    $key = 'unbilled';
                    $this->db->where('billable', 1);
                    $this->db->where('invoiceid IS NULL');

                    break;
            }
            $all_expenses = $this->db->get(db_prefix() . 'expenses')->result_array();
            $_total_all   = [];
            $cached_taxes = [];
            foreach ($all_expenses as $expense) {
                $_total = $expense['amount'];
                if ($expense['tax'] != 0) {
                    if (!isset($cached_taxes[$expense['tax']])) {
                        $tax                           = get_tax_by_id($expense['tax']);
                        $cached_taxes[$expense['tax']] = $tax;
                    } else {
                        $tax = $cached_taxes[$expense['tax']];
                    }
                    $_total += ($_total / 100 * $tax->taxrate);
                }
                if ($expense['tax2'] != 0) {
                    if (!isset($cached_taxes[$expense['tax2']])) {
                        $tax                            = get_tax_by_id($expense['tax2']);
                        $cached_taxes[$expense['tax2']] = $tax;
                    } else {
                        $tax = $cached_taxes[$expense['tax2']];
                    }
                    $_total += ($expense['amount'] / 100 * $tax->taxrate);
                }
                array_push($_total_all, $_total);
            }
            $_result[$key]['total'] = app_format_money(array_sum($_total_all), $currency);
        }
        $_result['currency_switcher'] = $currency_switcher;
        $_result['currencyid']        = $currencyid;

        // All expenses
        /*   $this->db->select('amount,tax');
        $this->db->where('currency',$currencyid);


        $this->db->select('amount,tax,invoiceid');
        $this->db->where('currency',$currencyid);
        $this->db->where('billable',1);
        $this->db->where('invoiceid IS NOT NULL');

        $all_expenses = $this->db->get(db_prefix().'expenses')->result_array();
        $_total_all = array();
        foreach($all_expenses as $expense){
        $_total = 0;
        if(total_rows(db_prefix().'invoices',array('status'=>2,'id'=>$expense['invoiceid'])) > 0){
        $_total = $expense['amount'];
        if($expense['tax'] != 0){
        $tax = get_tax_by_id($expense['tax']);
        $_total += ($_total / 100 * $tax->taxrate);
        }
        }
        array_push($_total_all,$_total);
        }
        $_result['billed']['total'] = app_format_money(array_sum($_total_all), $currency);

        $this->db->select('amount,tax,invoiceid');
        $this->db->where('currency',$currencyid);
        $this->db->where('billable',1);
        $this->db->where('invoiceid IS NOT NULL');

        $all_expenses = $this->db->get(db_prefix().'expenses')->result_array();
        $_total_all = array();
        foreach($all_expenses as $expense){
        $_total = 0;
        if(total_rows(db_prefix().'invoices','status NOT IN(2,5) AND id ='.$expense['invoiceid']) > 0){
        echo $this->db->last_query();
        $_total = $expense['amount'];
        if($expense['tax'] != 0){
        $tax = get_tax_by_id($expense['tax']);
        $_total += ($_total / 100 * $tax->taxrate);
        }
        }
        array_push($_total_all,$_total);
        }
        $_result['unbilled']['total'] = app_format_money(array_sum($_total_all), $currency);*/

        return $_result;
    }

    /**
     * Update expense
     * @param  mixed $data All $_POST data
     * @param  mixed $id   expense id to update
     * @return boolean
     */
    public function update($data, $id)
    {
        $affectedRows = 0;
        $data['date'] = to_sql_date($data['date']);
        $data['note'] = nl2br($data['note']);
        if (isset($data['billable'])) {
            $data['billable'] = 1;
        } else {
            $data['billable'] = 0;
        }

        if (isset($data['reimbursement'])) {
            $data['reimbursement'] = 1;
        } else {
            $data['reimbursement'] = 0;
        }

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'expenses', $data);
        if ($this->db->affected_rows() > 0) {
            log_activity('Expense Updated [' . $id . ']');
            $affectedRows++;
        }

        if ($affectedRows > 0) {
            return true;
        }

        return false;
    }

    /**
     * @param  integer ID
     * @return mixed
     * Delete expense from database, if used return
     */
    public function delete($id, $simpleDelete = false)
    {
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'expenses');
        if ($this->db->affected_rows() > 0) {
            $this->delete_expense_attachment($id);
            log_activity('Expense Deleted [' . $id . ']');
            return true;
        }
        return false;
    }

    public function delete_expense_attachment($id)
    {
        if (is_dir(get_upload_path_by_type('expense') . $id)) {
            if (delete_dir(get_upload_path_by_type('expense') . $id)) {
                $this->db->where('rel_id', $id);
                $this->db->where('rel_type', 'expense');
                $this->db->delete(db_prefix() . 'files');
                log_activity('Expense Receipt Deleted [ExpenseID: ' . $id . ']');

                return true;
            }
        }

        return false;
    }

    /* Categories start */

    /**
     * Get expense category
     * @param  mixed $id category id (Optional)
     * @return mixed     object or array
     */
    public function get_category($id = '')
    {
        if (is_numeric($id)) {
            $this->db->where('id', $id);

            return $this->db->get(db_prefix() . 'expenses_categories')->row();
        }
        $this->db->order_by('name', 'asc');

        return $this->db->get(db_prefix() . 'expenses_categories')->result_array();
    }

    /**
     * Add new expense category
     * @param mixed $data All $_POST data
     * @return boolean
     */
    public function add_category($data)
    {
        $data['description'] = nl2br($data['description']);
        $this->db->insert(db_prefix() . 'expenses_categories', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            log_activity('New Expense Category Added [ID: ' . $insert_id . ']');

            return $insert_id;
        }

        return false;
    }

    /**
     * Update expense category
     * @param  mixed $data All $_POST data
     * @param  mixed $id   expense id to update
     * @return boolean
     */
    public function update_category($data, $id)
    {
        $data['description'] = nl2br($data['description']);
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'expenses_categories', $data);
        if ($this->db->affected_rows() > 0) {
            log_activity('Expense Category Updated [ID: ' . $id . ']');

            return true;
        }

        return false;
    }

    /**
     * @param  integer ID
     * @return mixed
     * Delete expense category from database, if used return array with key referenced
     */
    public function delete_category($id)
    {
        if (is_reference_in_table('category', db_prefix() . 'expenses', $id)) {
            return [
                'referenced' => true,
            ];
        }
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'expenses_categories');
        if ($this->db->affected_rows() > 0) {
            log_activity('Expense Category Deleted [' . $id . ']');

            return true;
        }

        return false;
    }

    public function get_expenses_years()
    {
        return $this->db->query('SELECT DISTINCT(YEAR(date)) as year FROM ' . db_prefix() . 'expenses ORDER by year DESC')->result_array();
    }

    public function get_merchants($id = '')
    {
        if (is_numeric($id)) {
            $this->db->where('id', $id);
            return $this->db->get(db_prefix() . 'expense_merchant')->row();
        }
        $this->db->order_by('name', 'asc');
        return $this->db->get(db_prefix() . 'expense_merchant')->result_array();
    }

    public function get_reports()
    {
        $has_permission = has_permission('expense_reports', '', 'view');
        $staff_id = get_staff_user_id();
        if (!$has_permission) {
            $this->db->where('created_by', $staff_id);
        }
        $this->db->order_by('id', 'desc');
        $this->db->where_in('status', ["Draft", "Rejected"]);
        $query = $this->db->get(db_prefix() . 'expense_reports');
        return $query->result_array();
    }

    public function search_city($data)
    {
        $this->db->select("id, CONCAT(city, ', ', country) AS name");
        $this->db->like('city', $data['q']);
        $this->db->or_like('city', $data['q']);
        $query = $this->db->get(db_prefix() . 'cities');
        return $query->result_array();
    }

    public function selected_city($id)
    {
        $this->db->select("id, CONCAT(city, ', ', country) AS name");
        $this->db->where('id', $id);
        $query = $this->db->get(db_prefix() . 'cities');
        return $query->row_array();
    }

    public function get_expense_attachment($id)
    {
        $this->db->where('rel_id', $id);
        $this->db->where('rel_type', 'expense');
        $this->db->order_by('id', 'desc');
        return $this->db->get(db_prefix() . 'files')->row();
    }

    public function update_manually($id, $data)
    {
        $affectedRows = 0;
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'expenses', $data);
        if ($this->db->affected_rows() > 0) {
            $affectedRows++;
        }

        if ($affectedRows > 0) {
            return true;
        }

        return false;
    }
}
