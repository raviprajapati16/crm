<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Contact_book_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get_lists()
    {
        $this->db->select('*');
        $this->db->from(db_prefix() . 'contact_book');
        return $this->db->get()->result_array();
    }


    public function get($id)
    {
         log_message('error', $id);
        $this->db->select('*');
        $this->db->where('id', $id);
        $this->db->from(db_prefix() . 'contact_book');
        return $this->db->get()->row_array();
    }

    public function insert($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['created_by'] = get_staff_user_id();
        $this->db->insert(db_prefix() . 'contact_book', $data);
        $id = $this->db->insert_id();
        if ($id) {
            log_activity('Contact Book New Record Created [ID: ' . $id . ']');
            return $id;
        }
        return false;
    }

    public function update($id, $data)
    {
        $table = db_prefix() . 'contact_book';
        $this->db->where('id', (int) $id);
        $this->db->update($table, $data);
        if ($this->db->affected_rows() > 0) {
            log_activity('Contact Book Record Update [ID: ' . $id . ']');
            return true;
        }
        return false;
    }

    public function delete($id)
    {
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'contact_book');
        if ($this->db->affected_rows() > 0) {
            log_activity('Contact Book Record Deleted [ID: ' . $id . ']');
            return true;
        }
        return false;
    }

    public function add_attachment($data)
    {
        $this->db->insert('contact_book_attachments', $data);
        return $this->db->insert_id();
    }

    public function get_attachment($id)
    {
        $this->db->where('id', $id);
        return $this->db->get('contact_book_attachments')->row();
    }

    public function update_attachment($id, $data)
    {
        $this->db->where('id', $id);
        return $this->db->update('contact_book_attachments', $data);
    }

    public function delete_attachment($id)
    {
        $this->db->where('id', $id);
        return $this->db->delete('contact_book_attachments');
    }

    public function get_contact_attachments($contactId)
    {
        $this->db->where('contact_id', $contactId);
        return $this->db->get('contact_book_attachments')->result_array();
    }

    public function delete_contact_attachments($contactId)
    {
        $attachments = $this->get_contact_attachments($contactId);

        foreach ($attachments as $attachment) {
            $file_path = "uploads/contact_book/$contactId/" . $attachment['file_name'];
            if (file_exists($file_path)) {
                @unlink($file_path);
            }
        }

        $this->db->where('contact_id', $contactId);
        return $this->db->delete('contact_book_attachments');
    }
}
