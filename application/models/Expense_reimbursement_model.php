<?php

class Expense_reimbursement_model extends App_Model
{
    public function add_reimbursement($data)
    {
        $this->db->insert(db_prefix() . 'expense_reimbursement', $data);
        if ($this->db->affected_rows() > 0) {
            $id = $this->db->insert_id();
            log_activity('Expense Report : Reimbursement recorded ID ' . $id . ' for expense report ID ' . $data['report_id']);
            return $id;
        }
        return false;
    }

    public function get_reimbursement_by_report($report_id)
    {
        $this->db->where('report_id', $report_id);
        return $this->db->get(db_prefix() . 'expense_reimbursement')->row_array();
    }

    public function get_reimbursement($reimbursement_id)
    {
        $this->db->where('id', $reimbursement_id);
        $result = $this->db->get(db_prefix() . 'expense_reimbursement')->row_array();
        return $result ? $result : null;
    }

    public function delete_reimbursement($reimbursement_id)
    {
        $this->db->where('id', $reimbursement_id);
        $this->db->delete(db_prefix() . 'expense_reimbursement');
        return $this->db->affected_rows() > 0;
    }

    public function get_total_reimbursed_amount($report_id)
    {
        $this->db->select_sum('amount');
        $this->db->where('report_id', $report_id);
        $this->db->where('type', 'reimbursement');
        $result = $this->db->get(db_prefix() . 'expense_reimbursement')->row_array();
        return $result['amount'] ? (float)$result['amount'] : 0;
    }

    public function get_total_refund_amount($report_id)
    {
        $this->db->select_sum('amount');
        $this->db->where('report_id', $report_id);
        $this->db->where('type', 'refund');
        $result = $this->db->get(db_prefix() . 'expense_reimbursement')->row_array();
        return $result['amount'] ? (float)$result['amount'] : 0;
    }
}
