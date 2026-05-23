<?php

class Webmailsettings_model extends App_Model
{
    function update_webmail_signature($data)
    {
        if (!isset($data['user_id'])) {
            $user_id = get_staff_user_id();
        } else {
            $user_id = $data['user_id'];
        }
        $this->db->where('user_id', $user_id);
        $query = $this->db->get(db_prefix() . 'webmail_signatures');
        if ($query->num_rows() > 0) {
            $data['updated_at'] = date('Y-m-d H:i:s');
            $this->db->where('user_id', $user_id);
            $this->db->update(db_prefix() . 'webmail_signatures', $data);
            log_activity("Web mail signature updated by USER ID [" . $user_id . "] Record ID [" . $data['id'] . "]");
            if ($this->db->affected_rows() > 0) {
                return true;
            }
        } else {
            $data['user_id'] = $user_id;
            $data['created_at'] = date('Y-m-d H:i:s');
            $this->db->insert(db_prefix() . 'webmail_signatures', $data);
            $id = $this->db->insert_id();
            log_activity("Web mail signature updated by USER ID [" . $user_id . "] Record ID [" . $id . "]");
            return $id;
        }
        return false;
    }
}
