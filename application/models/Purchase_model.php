<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Purchase_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function add($data)
    {

        $data['address'] = trim($data['address']);
        $data['address'] = nl2br($data['address']);
        $data['datecreated'] = date('Y-m-d H:i:s');
        $data['addedfrom'] = get_staff_user_id();
        $data['hash'] = app_generate_hash();

        $items = [];
        if (isset($data['newitems'])) {
            $items = $data['newitems'];
            unset($data['newitems']);
        }

        if (isset($data['item_group'])) {
            unset($data['item_group']);
        }

        if (isset($data['item_sub_group'])) {
            unset($data['item_sub_group']);
        }

        if (isset($data['capacity'])) {
            unset($data['capacity']);
        }

        if (isset($data['hsn_code'])) {
            unset($data['hsn_code']);
        }

        $hook = hooks()->apply_filters('before_create_purchase', [
            'data' => $data,
            'items' => $items,
        ]);

        $data = $hook['data'];
        $items = $hook['items'];

        $data['content'] = get_option('purchase_terms_and_condition');
        $this->db->insert(db_prefix() . 'purchase', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            foreach ($items as $key => $item) {
                if ($itemid = add_new_sales_item_post($item, $insert_id, 'purchase')) {
                    _maybe_insert_post_item_tax($itemid, $item, $insert_id, 'purchase');
                }
            }
            $purchase = $this->get($insert_id);
            if ($purchase->assigned != 0) {
                if ($purchase->assigned != get_staff_user_id()) {
                    $notified = add_notification([
                        'description' => 'not_purchase_assigned_to_you',
                        'touserid' => $purchase->assigned,
                        'fromuserid' => get_staff_user_id(),
                        'link' => 'purchase/list_purchase/' . $insert_id,
                        'additional_data' => serialize([
                            $purchase->subject,
                        ]),
                    ]);
                    if ($notified) {
                        pusher_trigger_notification([$purchase->assigned]);
                    }
                }
            }
            log_activity('New purchase Created [ID: ' . $insert_id . ']');
            return $insert_id;
        }

        return false;
    }

    public function update($data, $id)
    {
        $affectedRows = 0;

        $current_purchase = $this->get($id);

        $items = [];
        if (isset($data['items'])) {
            $items = $data['items'];
            unset($data['items']);
        }

        $newitems = [];
        if (isset($data['newitems'])) {
            $newitems = $data['newitems'];
            unset($data['newitems']);
        }

        if (isset($data['item_group'])) {
            unset($data['item_group']);
        }

        if (isset($data['item_sub_group'])) {
            unset($data['item_sub_group']);
        }

        if (isset($data['capacity'])) {
            unset($data['capacity']);
        }

        if (isset($data['hsn_code'])) {
            unset($data['hsn_code']);
        }

        if (isset($data['tags'])) {
            if (handle_tags_save($data['tags'], $id, 'purchase')) {
                $affectedRows++;
            }
        }

        $data['address'] = trim($data['address']);
        $data['address'] = nl2br($data['address']);

        $hook = hooks()->apply_filters('before_purchase_updated', [
            'data' => $data,
            'items' => $items,
            'newitems' => $newitems,
            'removed_items' => isset($data['removed_items']) ? $data['removed_items'] : [],
        ], $id);

        $data = $hook['data'];
        $data['removed_items'] = $hook['removed_items'];
        $newitems = $hook['newitems'];
        $items = $hook['items'];

        // Delete items checked to be removed from database
        foreach ($data['removed_items'] as $remove_item_id) {
            if (handle_removed_sales_item_post($remove_item_id, 'purchase')) {
                $affectedRows++;
            }
        }

        unset($data['removed_items']);
        $data['updated_by'] = get_staff_user_id();
        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'purchase', $data);
        if ($this->db->affected_rows() > 0) {
            $affectedRows++;
            $purchase_now = $this->get($id);
            if ($current_purchase->assigned != $purchase_now->assigned) {
                if ($purchase_now->assigned != get_staff_user_id()) {
                    $notified = add_notification([
                        'description' => 'not_purchase_assigned_to_you',
                        'touserid' => $purchase_now->assigned,
                        'fromuserid' => get_staff_user_id(),
                        'link' => 'purchase/list_purchase/' . $id,
                        'additional_data' => serialize([
                            $purchase_now->subject,
                        ]),
                    ]);
                    if ($notified) {
                        pusher_trigger_notification([$purchase_now->assigned]);
                    }
                }
            }
        }

        foreach ($items as $key => $item) {
            if (update_sales_item_post($item['itemid'], $item)) {
                $affectedRows++;
            }
        }

        foreach ($newitems as $key => $item) {
            if ($new_item_added = add_new_sales_item_post($item, $id, 'purchase')) {
                _maybe_insert_post_item_tax($new_item_added, $item, $id, 'purchase');
                $affectedRows++;
            }
        }

        if ($affectedRows > 0) {
            log_activity('purchase Updated [ID:' . $id . ']');
        }


        if ($affectedRows > 0) {
            return true;
        }

        return false;
    }

    public function get($id = '', $where = [])
    {
        $this->db->where($where);
        $this->db->select('*,' . db_prefix() . 'currencies.id as currencyid, ' . db_prefix() . 'purchase.id as id, ' . db_prefix() . 'currencies.name as currency_name');
        $this->db->from(db_prefix() . 'purchase');
        $this->db->join(db_prefix() . 'currencies', db_prefix() . 'currencies.id = ' . db_prefix() . 'purchase.currency', 'left');

        if (is_numeric($id)) {
            $this->db->where(db_prefix() . 'purchase.id', $id);
            $purchase = $this->db->get()->row();
            if ($purchase) {
                $purchase->attachments = $this->get_attachments($id);
                $purchase->items = get_items_by_type('purchase', $id);
            }

            return $purchase;
        }

        return $this->db->get()->result_array();
    }

    public function get_attachments($purchase_id, $id = '')
    {
        if (is_numeric($id)) {
            $this->db->where('id', $id);
            $this->db->where('rel_type', 'purchase');
            $result = $this->db->get(db_prefix() . 'files');
            return $result->row();
        } else {
            return get_attachments_by_type('purchase', $purchase_id);
        }
    }

    public function delete_attachment($id)
    {
        $attachment = $this->get_attachments('', $id);
        $deleted = false;
        if ($attachment) {
            if (empty($attachment->external)) {
                unlink(get_upload_path_by_type('purchase') . $attachment->rel_id . '/' . $attachment->file_name);
            }
            $this->db->where('id', $attachment->id);
            $this->db->delete(db_prefix() . 'files');
            if ($this->db->affected_rows() > 0) {
                $deleted = true;
                log_activity('purchase Attachment Deleted [ID: ' . $attachment->rel_id . ']');
            }
            if (is_dir(get_upload_path_by_type('purchase') . $attachment->rel_id)) {
                // Check if no attachments left, so we can delete the folder also
                $other_attachments = list_files(get_upload_path_by_type('purchase') . $attachment->rel_id);
                if (count($other_attachments) == 0) {
                    // okey only index.html so we can delete the folder also
                    delete_dir(get_upload_path_by_type('purchase') . $attachment->rel_id);
                }
            }
        }

        return $deleted;
    }

    public function delete($id)
    {
        $data['deleted_at'] = date('Y-m-d H:i:s');
        $data['deleted_by'] = get_staff_full_name();
        $data['purchase_number_prefix'] = null;
        $data['purchase_number'] = null;
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'purchase', $data);
        if ($this->db->affected_rows() > 0) {
            log_activity('purchase Deleted [purchaseID:' . $id . ']');
            return true;
        }
        return false;
    }

    public function send_purchase_to_email($id, $attachpdf = true, $cc = '')
    {
        $purchase = $this->get($id);
        $staffid = get_staff_user_id();
        $staff = $this->staff_model->get($staffid, ['active' => 1]);
        $sent = send_mail_template('purchase_send_to_vendor', $purchase, $attachpdf, $cc, $staff->email);
     
                
             if ($sent) {
                        $this->db->where('id', $id);
                        $this->db->update(db_prefix() . 'purchase', [
                            'status' => '3'
                        ]);
                   return true;
                    }
           
    
        return false;
    }

    public function update_purchase($data, $id)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'purchase', $data);
        $affectedRows = $this->db->affected_rows();
        if ($affectedRows > 0) {
            return true;
        }
        return false;
    }

    public function get_sale_agents()
    {
        return $this->db->query('SELECT DISTINCT(assigned) as sale_agent FROM ' . db_prefix() . 'purchase WHERE assigned != 0')->result_array();
    }

    public function get_purchase_years()
    {
        return $this->db->query('SELECT DISTINCT(YEAR(date)) as year FROM ' . db_prefix() . 'purchase')->result_array();
    }

    public function get_relation_data_values($vendor_id)
    {
        $this->db->where('id', $vendor_id);
        $this->db->where('is_vendor', '1');
        $_data = $this->db->get(db_prefix() . 'leads')->row();
        if (!empty($_data)) {
            $data = new StdClass();
            $data->phone = $_data->phonenumber;
            $data->to = $_data->name;
            if (!empty($_data->company)) {
                $data->to = $_data->company;
            }
            $data->company = $_data->company;
            $data->address = $_data->address;
            $data->email = $_data->email;
            $data->zip = $_data->zip;
            $data->country = $_data->country;
            $data->state = $_data->state;
            $data->city = $_data->city;
            $data->gst_in = $_data->gst_in;
            return $data;
        }
        return [];
    }

    public function get_statuses($id = '', $where = [])
    {
        $this->db->where($where);
        if (is_numeric($id)) {
            $this->db->where('id', $id);
            return $this->db->get(db_prefix() . 'purchase_status')->row();
        }
        $this->db->order_by("name = 'Completed'", 'ASC');
        $this->db->order_by('statusorder', 'ASC');
        return $this->db->get(db_prefix() . 'purchase_status')->result_array();
    }

    public function add_status($data)
    {
        if (isset($data['color']) && $data['color'] == '') {
            $data['color'] = '#757575';
        }

        if (!isset($data['statusorder'])) {
            $data['statusorder'] = total_rows(db_prefix() . 'purchase_status') + 1;
        }
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['created_by'] = get_staff_user_id();

        if (isset($data['isdefault'])) {
            $this->db->update(db_prefix() . 'purchase_status', ['isdefault' => 0]);
            $data['isdefault'] = 1;
        }

        $this->db->insert(db_prefix() . 'purchase_status', $data);
        $insert_id = $this->db->insert_id();

        if ($insert_id) {
            log_activity('New Purchase Status Added [StatusID: ' . $insert_id . ', Name: ' . $data['name'] . ']');
            return $insert_id;
        }

        return false;
    }

    public function update_status($data, $id)
    {
        if (isset($data['isdefault'])) {
            $this->db->update(db_prefix() . 'purchase_status', ['isdefault' => 0]);
            $data['isdefault'] = 1;
        }

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'purchase_status', $data);

        if ($this->db->affected_rows() > 0) {
            log_activity('Purchase Status Updated [StatusID: ' . $id . ', Name: ' . $data['name'] . ']');
            return true;
        }

        return false;
    }

    public function delete_status($id)
    {
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'purchase_status');
        if ($this->db->affected_rows() > 0) {
            log_activity('Purchase Status Deleted [StatusID: ' . $id . ']');
            return true;
        }
        return false;
    }
}
