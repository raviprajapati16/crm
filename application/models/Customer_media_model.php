<?php

class Customer_media_model extends App_Model
{

    public function get($id = '')
    {
        if (is_numeric($id)) {
            $this->db->where('id', $id);
            return $this->db->get(db_prefix() . 'customer_media')->row_array();
        } else {
            return $this->db->get(db_prefix() . 'customer_media')->result_array();
        }
        return [];
    }

    public function exists($customer_id, $rel_type, $rel_id, $id = '')
    {
        $this->db->where('customer_id', $customer_id);
        $this->db->where('rel_type', $rel_type);
        $this->db->where('rel_id', $rel_id);
        if ($id != '') {
            $this->db->where('id !=', $id);
        }
        return $this->db->get(db_prefix() . 'customer_media')->num_rows() > 0;
    }

    public function add($data)
    {
        $data['created_by'] = get_staff_user_id();
        $data['created_at'] = date('Y-m-d H:i:s');
        $this->db->insert(db_prefix() . 'customer_media', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            log_activity("New Customer Media Created. ID [$insert_id]");
            return $insert_id;
        }
        return false;
    }

    public function update($id, $data)
    {
        $this->db->where('id', $id);
        $check = $this->db->update(db_prefix() . 'customer_media', $data);
        if ($check) {
            log_activity("Customer Media Updated. ID [$id]");
            return true;
        }
        return false;
    }

    public function delete($id)
    {
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'customer_media');
        if ($this->db->affected_rows() > 0) {
            log_activity("Customer Media Deleted. ID [$id]");
            return true;
        }
        return false;
    }

    public function get_customer_media($customer_id)
    {
        $sql = "
    WITH FilteredMedia AS (
        SELECT
            cm.id,
            cm.customer_id,
            cm.rel_type,
            cm.rel_id,
            cm.created_at,
            cm.created_by,

            CASE
                WHEN cm.rel_type = 'brochure' THEN b.title
                WHEN cm.rel_type = 'product_presentation' THEN pp.title
                WHEN cm.rel_type = 'tutorial' THEN t.title
                ELSE NULL
            END AS title,
            CASE
                WHEN cm.rel_type = 'tutorial' THEN t.link
                WHEN cm.rel_type = 'product_presentation' THEN pp.file_name
                WHEN cm.rel_type = 'brochure' THEN b.file_name
                ELSE NULL
            END AS media_file,
            CASE
                WHEN cm.rel_type = 'product_presentation' THEN pp.hash
                WHEN cm.rel_type = 'brochure' THEN b.hash
                ELSE NULL
            END AS hash
        FROM tblcustomer_media cm
        LEFT JOIN tblbrochure b ON cm.rel_type = 'brochure' AND cm.rel_id = b.id
        LEFT JOIN tblproduct_presentation pp ON cm.rel_type = 'product_presentation' AND cm.rel_id = pp.id
        LEFT JOIN tbltutorial_videos t ON cm.rel_type = 'tutorial' AND cm.rel_id = t.id
        WHERE cm.customer_id = ?
    ),
    TotalCount AS (
        SELECT COUNT(*) AS total_count FROM FilteredMedia
    )
    SELECT
        *,
        (SELECT total_count FROM TotalCount) AS total_records
    FROM FilteredMedia";

        $params = [$customer_id];

        $query = $this->db->query($sql, $params);

        return $query->result_array();
    }
}
