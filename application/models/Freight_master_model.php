<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Freight_master_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get freight group by id or all groups
     * @param  mixed $id
     * @return mixed
     */
    public function get_groups($id = '')
    {
        if (is_numeric($id)) {
            $this->db->where('id', $id);
            return $this->db->get(db_prefix() . 'freight_groups')->row();
        }
        $this->db->order_by('name', 'asc');
        return $this->db->get(db_prefix() . 'freight_groups')->result_array();
    }

    /**
     * Add new freight group
     * @param array $data
     * @return mixed
     */
    public function add_group($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $this->db->insert(db_prefix() . 'freight_groups', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            log_activity('New Freight Group Added [ID:' . $insert_id . ', Name: ' . $data['name'] . ']');
            return $insert_id;
        }
        return false;
    }

    /**
     * Update group
     * @param  array $data
     * @param  mixed $id
     * @return boolean
     */
    public function update_group($data, $id)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'freight_groups', $data);
        if ($this->db->affected_rows() > 0) {
            log_activity('Freight Group Updated [ID:' . $id . ', Name: ' . $data['name'] . ']');
            return true;
        }
        return false;
    }

    /**
     * Delete group
     * @param  mixed $id
     * @return boolean
     */
    public function delete_group($id)
    {
        // Check if there are sizes assigned to this group
        $this->db->where('group_id', $id);
        $sizes = $this->db->get(db_prefix() . 'freight_sizes')->num_rows();
        if ($sizes > 0) {
            return array('referenced' => true);
        }

        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'freight_groups');
        if ($this->db->affected_rows() > 0) {
            log_activity('Freight Group Deleted [ID:' . $id . ']');
            return true;
        }
        return false;
    }

    /**
     * Get size by id or all sizes
     * @param  mixed $id
     * @return mixed
     */
    public function get_sizes($id = '')
    {
        $this->db->select(db_prefix() . 'freight_sizes.*, ' . db_prefix() . 'freight_groups.name as group_name');
        $this->db->join(db_prefix() . 'freight_groups', db_prefix() . 'freight_groups.id = ' . db_prefix() . 'freight_sizes.group_id', 'left');
        
        if (is_numeric($id)) {
            $this->db->where(db_prefix() . 'freight_sizes.id', $id);
            return $this->db->get(db_prefix() . 'freight_sizes')->row();
        }
        $this->db->order_by(db_prefix() . 'freight_sizes.name', 'asc');
        return $this->db->get(db_prefix() . 'freight_sizes')->result_array();
    }

    /**
     * Add new size
     * @param array $data
     * @return boolean
     */
    public function add_size($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $this->db->insert(db_prefix() . 'freight_sizes', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            log_activity('New Freight Size Added [ID:' . $insert_id . ', Name: ' . $data['name'] . ']');
            return true;
        }
        return false;
    }

    /**
     * Update size
     * @param  array $data
     * @param  mixed $id
     * @return boolean
     */
    public function update_size($data, $id)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'freight_sizes', $data);
        if ($this->db->affected_rows() > 0) {
            log_activity('Freight Size Updated [ID:' . $id . ', Name: ' . $data['name'] . ']');
            return true;
        }
        return false;
    }

    /**
     * Delete size
     * @param  mixed $id
     * @return boolean
     */
    public function delete_size($id)
    {
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'freight_sizes');
        if ($this->db->affected_rows() > 0) {
            log_activity('Freight Size Deleted [ID:' . $id . ']');
            return true;
        }
        return false;
    }
}
