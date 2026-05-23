<?php

defined('BASEPATH') or exit('No direct script access allowed');

class APIController extends App_Controller
{
    public function createNewUser()
    {
        if ($this->input->post()) {
            $post_data = $this->input->post();
            if (!$post_data['email']) {
                $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode(['status' => 'error', 'message' => 'Missing required parameter: email']));
                return;
            }

            if (!$post_data['firstname']) {
                $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode(['status' => 'error', 'message' => 'Missing required parameter: firstname']));
                return;
            }

            if (!$post_data['lastname']) {
                $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode(['status' => 'error', 'message' => 'Missing required parameter: lastname']));
                return;
            }

            if (!$post_data['birthday']) {
                $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode(['status' => 'error', 'message' => 'Missing required parameter: DOB']));
                return;
            }

            if (!$post_data['doj']) {
                $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode(['status' => 'error', 'message' => 'Missing required parameter: DOJ']));
                return;
            }

            if (!$post_data['password']) {
                $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode(['status' => 'error', 'message' => 'Missing required parameter: Password']));
                return;
            }
            $this->load->database();
            $this->db->where('email', $post_data['email']);
            $this->db->where('active', 1);
            $query = $this->db->get(db_prefix() . 'staff');

            if ($query->num_rows() > 0) {
                $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode(['status' => 'error', 'message' => 'Staff account already active in CRM with same email.']));
                return;
            }

            $post_data['active'] = 1;
            $post_data['role'] = 2; //executive
            $post_data['two_factor_auth_enabled'] = 1;
            $post_data['datecreated'] = date('Y-m-d H:i:s');

            // echo "<pre>";
            // print_r($post_data);
            // exit;

            if ($this->db->insert(db_prefix() . 'staff', $post_data)) {

                $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode(['status' => 'success', 'message' => 'User created successfully']));
            } else {
                $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode(['status' => 'error', 'message' => 'Failed to create user']));
            }
        }
    }
}
