<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Freights_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get freight by id or all freights
     * @param  mixed $id
     * @return mixed
     */
    public function get($id = '')
    {
        $this->db->select(db_prefix() . 'freights.*, ' . 
            db_prefix() . 'freight_groups.name as group_name, ' . 
            db_prefix() . 'freight_sizes.name as size_name');
        
        $this->db->join(db_prefix() . 'freight_groups', db_prefix() . 'freight_groups.id = ' . db_prefix() . 'freights.group_id', 'left');
        $this->db->join(db_prefix() . 'freight_sizes', db_prefix() . 'freight_sizes.id = ' . db_prefix() . 'freights.size_id', 'left');

        if (is_numeric($id)) {
            $this->db->where(db_prefix() . 'freights.id', $id);
            return $this->db->get(db_prefix() . 'freights')->row();
        }
        
        return $this->db->get(db_prefix() . 'freights')->result_array();
    }

    /**
     * Add new freight record
     * @param array $data
     * @return mixed
     */
    public function add($data)
    {
        $data['created_by'] = get_staff_user_id();
        $data['created_at'] = date('Y-m-d H:i:s');
        
        $this->db->insert(db_prefix() . 'freights', $data);
        $insert_id = $this->db->insert_id();
        
        if ($insert_id) {
            log_activity('New Freight Record Added [ID: ' . $insert_id . ']');
            return $insert_id;
        }
        return false;
    }

    /**
     * Update freight record
     * @param  array $data
     * @param  mixed $id
     * @return boolean
     */
    public function update($data, $id)
    {
        $original = $this->get($id);
        
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'freights', $data);
        
        if ($this->db->affected_rows() > 0) {
            log_activity('Freight Record Updated [ID: ' . $id . ']');
            
            // Map DB fields to human readable labels
            $field_labels = [
                'from_country' => 'From Country',
                'from_state'   => 'From State',
                'from_city'    => 'From City/Port',
                'from_pin'     => 'From Pin Code',
                'to_country'   => 'To Country',
                'to_state'     => 'To State',
                'to_city'      => 'To City/Port',
                'to_pin'       => 'To Pin Code',
                'group_id'     => 'Truck/Container Group',
                'size_id'      => 'Truck/Container Size',
                'carrier'      => 'Carrier',
                'freight_cost' => 'Freight (Cost/Price)',
                'transit_time' => 'Transit Time',
                'notes'        => 'Notes'
            ];

            // Log specific changes
            if ($original) {
                foreach ($data as $key => $val) {
                    if (isset($original->$key) && $original->$key != $val) {
                        $label = isset($field_labels[$key]) ? $field_labels[$key] : $key;
                        $desc = "Updated $label from '{$original->$key}' to '$val'";
                        $this->log_activity($id, $desc);
                    }
                }
            }
            return true;
        }
        return false;
    }

    public function log_activity($id, $description, $additional_data = '')
    {
        $log = [
            'date'            => date('Y-m-d H:i:s'),
            'description'     => $description,
            'freight_id'      => $id,
            'staffid'         => get_staff_user_id(),
            'additional_data' => $additional_data,
            'full_name'       => get_staff_full_name(get_staff_user_id()),
        ];
        $this->db->insert(db_prefix() . 'freight_activity_log', $log);
        return $this->db->insert_id();
    }

    public function get_activity_log($id)
    {
        $this->db->where('freight_id', $id);
        $this->db->order_by('date', 'desc');
        return $this->db->get(db_prefix() . 'freight_activity_log')->result_array();
    }

    /**
     * Delete freight record
     * @param  mixed $id
     * @return boolean
     */
    public function delete($id)
    {
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'freights');
        
        if ($this->db->affected_rows() > 0) {
            log_activity('Freight Record Deleted [ID: ' . $id . ']');
            return true;
        }
        return false;
    }
}
