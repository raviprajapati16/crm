<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Freight_master extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('freight_master_model');
    }

    /* List all freight groups and sizes */
    public function manage()
    {
        if (!is_admin()) {
            access_denied('Freight Master');
        }
        
        $data['title'] = 'Freight Master';
        $data['groups'] = $this->freight_master_model->get_groups();
        $this->load->view('admin/freight_master/manage', $data);
    }

    /* Get groups table */
    public function groups_table()
    {
        if (!is_admin()) {
            ajax_access_denied();
        }
        $this->app->get_table_data('freight_groups');
    }

    /* Get sizes table */
    public function sizes_table()
    {
        if (!is_admin()) {
            ajax_access_denied();
        }
        $this->app->get_table_data('freight_sizes');
    }

    /* Add or update freight group */
    public function group()
    {
        if (!is_admin()) {
            access_denied('Freight Master');
        }
        if ($this->input->post()) {
            $data = $this->input->post();
            
            if (!$this->input->post('id')) {
                $success = $this->freight_master_model->add_group($data);
                if ($success) {
                    set_alert('success', _l('added_successfully', 'Freight Group'));
                }
            } else {
                $id = $data['id'];
                unset($data['id']);
                $success = $this->freight_master_model->update_group($data, $id);
                if ($success) {
                    set_alert('success', _l('updated_successfully', 'Freight Group'));
                }
            }
            die;
        }
    }

    /* Delete freight group */
    public function delete_group($id)
    {
        if (!is_admin()) {
            access_denied('Freight Master');
        }
        if (!$id) {
            redirect(admin_url('freight_master/manage?group=groups'));
        }
        $response = $this->freight_master_model->delete_group($id);
        if (is_array($response) && isset($response['referenced'])) {
            set_alert('warning', _l('is_referenced', 'Freight Group'));
        } elseif ($response == true) {
            set_alert('success', _l('deleted', 'Freight Group'));
        } else {
            set_alert('warning', _l('problem_deleting', 'Freight Group'));
        }
        redirect(admin_url('freight_master/manage?group=groups'));
    }

    /* Add or update freight size */
    public function size()
    {
        if (!is_admin()) {
            access_denied('Freight Master');
        }
        if ($this->input->post()) {
            $data = $this->input->post();
            
            if (!$this->input->post('id')) {
                $success = $this->freight_master_model->add_size($data);
                if ($success) {
                    set_alert('success', _l('added_successfully', 'Freight Size'));
                }
            } else {
                $id = $data['id'];
                unset($data['id']);
                $success = $this->freight_master_model->update_size($data, $id);
                if ($success) {
                    set_alert('success', _l('updated_successfully', 'Freight Size'));
                }
            }
            die;
        }
    }

    /* Delete freight size */
    public function delete_size($id)
    {
        if (!is_admin()) {
            access_denied('Freight Master');
        }
        if (!$id) {
            redirect(admin_url('freight_master/manage?group=sizes'));
        }
        $response = $this->freight_master_model->delete_size($id);
        if ($response == true) {
            set_alert('success', _l('deleted', 'Freight Size'));
        } else {
            set_alert('warning', _l('problem_deleting', 'Freight Size'));
        }
        redirect(admin_url('freight_master/manage?group=sizes'));
    }
}
