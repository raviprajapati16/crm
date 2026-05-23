<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Invoice_items extends AdminController
{
    private $not_importable_fields = ['id'];

    public function __construct()
    {
        parent::__construct();
        $this->load->model('invoice_items_model');
    }

    /* List all available items */
    public function index()
    {
        if (!has_permission('items', '', 'view')) {
            access_denied('Invoice Items');
        }

        $this->load->model('taxes_model');
        $data['taxes']        = $this->taxes_model->get();
        $data['items_groups'] = $this->invoice_items_model->get_groups();

        $this->load->model('currencies_model');
        $data['currencies'] = $this->currencies_model->get();

        $data['base_currency'] = $this->currencies_model->get_base_currency();
        $data['items_sub_groups'] = $this->invoice_items_model->get_subgroups();
        $data['title'] = _l('invoice_items');
        $this->load->view('admin/invoice_items/manage', $data);
    }

    public function table()
    {
        if (!has_permission('items', '', 'view')) {
            ajax_access_denied();
        }
        $this->app->get_table_data('invoice_items');
    }

    /* Edit or update items / ajax request /*/
    public function manage()
    {
        if (has_permission('items', '', 'view')) {
            if ($this->input->post()) {
                $data = $this->input->post();
                if ($data['itemid'] == '') {
                    if (!has_permission('items', '', 'create')) {
                        header('HTTP/1.0 400 Bad error');
                        echo _l('access_denied');
                        die;
                    }
                    $id      = $this->invoice_items_model->add($data);
                    $success = false;
                    $message = '';
                    if ($id) {
                        $success = true;
                        $message = _l('added_successfully', _l('sales_item'));
                    }
                    echo json_encode([
                        'success' => $success,
                        'message' => $message,
                        'item'    => $this->invoice_items_model->get($id),
                    ]);
                } else {
                    if (!has_permission('items', '', 'edit')) {
                        header('HTTP/1.0 400 Bad error');
                        echo _l('access_denied');
                        die;
                    }
                    $success = $this->invoice_items_model->edit($data);
                    $message = '';
                    if ($success) {
                        $message = _l('updated_successfully', _l('sales_item'));
                    }
                    echo json_encode([
                        'success' => $success,
                        'message' => $message,
                    ]);
                }
            }
        }
    }

    public function import()
    {
        if (!has_permission('items', '', 'create')) {
            access_denied('Items Import');
        }

        $this->load->library('import/import_items', [], 'import');

        $this->import->setDatabaseFields($this->db->list_fields(db_prefix() . 'items'))
            ->setCustomFields(get_custom_fields('items'));

        if ($this->input->post('download_sample') === 'true') {
            $this->import->downloadSample();
        }

        if (
            $this->input->post()
            && isset($_FILES['file_csv']['name']) && $_FILES['file_csv']['name'] != ''
        ) {
            $this->import->setSimulation($this->input->post('simulate'))
                ->setTemporaryFileLocation($_FILES['file_csv']['tmp_name'])
                ->setFilename($_FILES['file_csv']['name'])
                ->perform();

            $data['total_rows_post'] = $this->import->totalRows();

            if (!$this->import->isSimulation()) {
                set_alert('success', _l('import_total_imported', $this->import->totalImported()));
            }
        }

        $data['title'] = _l('import');
        $this->load->view('admin/invoice_items/import', $data);
    }

    public function add_group()
    {
        if ($this->input->post() && has_permission('items', '', 'create')) {
            $this->invoice_items_model->add_group($this->input->post());
            set_alert('success', _l('added_successfully', _l('item_group')));
        }
    }

    public function update_group($id)
    {
        if ($this->input->post() && has_permission('items', '', 'edit')) {
            $this->invoice_items_model->edit_group($this->input->post(), $id);
            set_alert('success', _l('updated_successfully', _l('item_group')));
        }
    }

    public function delete_group($id)
    {
        if (has_permission('items', '', 'delete')) {
            if ($this->invoice_items_model->delete_group($id)) {
                set_alert('success', _l('deleted', _l('item_group')));
            }
        }
        redirect(admin_url('invoice_items?groups_modal=true'));
    }

    /* Delete item*/
    public function delete($id)
    {
        if (!has_permission('items', '', 'delete')) {
            access_denied('Invoice Items');
        }

        if (!$id) {
            redirect(admin_url('invoice_items'));
        }


        $error = "Sorry! This product used in ";
        $total = 0;

        // proposal check
        $this->db->where('itemable.item_id', $id);
        $this->db->where('itemable.rel_type', 'proposal');
        $this->db->where('proposals.deleted_at IS NULL');
        $this->db->from(db_prefix(). 'itemable itemable');
        $this->db->join(db_prefix() . 'proposals proposals', 'itemable.rel_id = proposals.id');
        $proposal_count = $this->db->count_all_results();
        if($proposal_count > 0){
            set_alert('warning',"Sorry! This product used in proposal");
            redirect(admin_url('invoice_items'));
        }

        // Invoice check
        $this->db->where('itemable.item_id', $id);
        $this->db->where('itemable.rel_type', 'invoice');
        $this->db->where('invoices.deleted_at IS NULL');
        $this->db->from(db_prefix(). 'itemable itemable');
        $this->db->join(db_prefix() . 'invoices invoices', 'itemable.rel_id = invoices.id');
        $invoice_count = $this->db->count_all_results();
        if($invoice_count > 0){
            set_alert('warning',"Sorry! This product used in invoice");
            redirect(admin_url('invoice_items'));
        }

        //Purchase check
        $this->db->where('itemable.item_id', $id);
        $this->db->where('itemable.rel_type', 'purchase');
        $this->db->where('purchase.deleted_at IS NULL');
        $this->db->from(db_prefix(). 'itemable itemable');
        $this->db->join(db_prefix() . 'purchase purchase', 'itemable.rel_id = purchase.id');
        $purchase_count = $this->db->count_all_results();
        if($purchase_count > 0){
            set_alert('warning',"Sorry! This product used in purchase");
            redirect(admin_url('invoice_items'));
        }


        $response = $this->invoice_items_model->delete($id);
        if (is_array($response) && isset($response['referenced'])) {
            set_alert('warning', _l('is_referenced', _l('invoice_item_lowercase')));
        } elseif ($response == true) {
            set_alert('success', _l('deleted', _l('invoice_item')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('invoice_item_lowercase')));
        }
        redirect(admin_url('invoice_items'));
    }

    public function bulk_action()
    {
        hooks()->do_action('before_do_bulk_action_for_items');
        $total_deleted = 0;
        if ($this->input->post()) {
            $ids                   = $this->input->post('ids');
            $has_permission_delete = has_permission('items', '', 'delete');
            if (is_array($ids)) {
                foreach ($ids as $id) {
                    if ($this->input->post('mass_delete')) {
                        if ($has_permission_delete) {
                            if ($this->invoice_items_model->delete($id)) {
                                $total_deleted++;
                            }
                        }
                    }
                }
            }
        }

        if ($this->input->post('mass_delete')) {
            set_alert('success', _l('total_items_deleted', $total_deleted));
        }
    }

    public function search()
    {
        if ($this->input->post() && $this->input->is_ajax_request()) {
            echo json_encode($this->invoice_items_model->search($this->input->post('q')));
        }
    }

    /* Get item by id / ajax */
    public function get_item_by_id($id)
    {
        if ($this->input->is_ajax_request()) {
            $item                     = $this->invoice_items_model->get($id);
            $item->long_description   = nl2br($item->long_description);
            $item->custom_fields_html = render_custom_fields('items', $id, [], ['items_pr' => true]);
            $item->custom_fields      = [];

            $cf = get_custom_fields('items');

            foreach ($cf as $custom_field) {
                $val = get_custom_field_value($id, $custom_field['id'], 'items_pr');
                if ($custom_field['type'] == 'textarea') {
                    $val = clear_textarea_breaks($val);
                }
                $custom_field['value'] = $val;
                $item->custom_fields[] = $custom_field;
            }

            $subgroups = $this->invoice_items_model->get_subgroup_by_group($item->group_id);

            $subgroup_options = "<option value=''></option>";
            if (count($subgroups) > 0) {
                foreach ($subgroups as $sgroup) {
                    $subgroup_options .= "<option value='" . $sgroup['id'] . "'>" . $sgroup['name'] . "</option>";
                }
            }

            $item->subgroup_options = $subgroup_options;
            echo json_encode($item);
        }
    }

    public function get_items_by_group($id, $subgroup_id = "")
    {
        if ($this->input->is_ajax_request()) {

            $items = $this->invoice_items_model->get_item_by_group($id, $subgroup_id);

            $html = "<option value=''></option>";
            if (count($items) > 0) {
                foreach ($items as $item) {
                    $item_name = ($item['capacity'] != "") ? $item['capacity'] . ' - ' . $item['description'] : $item['description'];
                    $html .= "<option value='" . $item['id'] . "'>" . $item_name . "</option>";
                }
            }
            echo json_encode($html);
        }
    }

    public function add_subgroup()
    {
        if ($this->input->post() && has_permission('items', '', 'create')) {
            $postData = $this->input->post();

            if (isset($postData['id']) && $postData['id'] != "") {
                $this->invoice_items_model->update_subgroup($postData);
                set_alert('success', 'sub group Update successfully');
            } else {
                $this->invoice_items_model->add_subgroup($postData);
                set_alert('success', 'sub group insert successfully');
            }

            redirect(admin_url('invoice_items?subgroup_modal=true'));
        }
    }

    public function edit_subgroup($id)
    {
        $groupdata = $this->invoice_items_model->get_subgroup_by_id($id);

        if ($groupdata) {
            $response['status'] = true;
            $response['data'] = $groupdata;
        } else {
            $response['status'] = false;
            $response['data'] = [];
        }

        echo json_encode($response);
        exit;
    }

    public function delete_subgroup($id)
    {

        if (!has_permission('items', '', 'delete')) {
            access_denied('Invoice Items');
        }

        if (!$id) {
            redirect(admin_url('invoice_items'));
        }

        $response = $this->invoice_items_model->delete_subgroup($id);

        if ($response == true) {
            set_alert('success', "Delete sub group successfully");
        } else {
            set_alert('warning', "somthing went wrong");
        }

        redirect(admin_url('invoice_items'));
    }

    public function get_subgroup_by_group($group_id)
    {

        if ($this->input->is_ajax_request()) {

            $subgroups = $this->invoice_items_model->get_subgroup_by_group($group_id);

            $html = "<option value=''></option>";
            if (count($subgroups) > 0) {
                foreach ($subgroups as $group) {
                    $html .= "<option value='" . $group['id'] . "'>" . $group['name'] . "</option>";
                }
            }
            echo json_encode($html);
            exit;
        }
    }
}
