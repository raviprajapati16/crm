<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Customer_media extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('customer_media_model');
        $this->load->model('clients_model');
        $this->load->model('product_presentation_model');
        $this->load->model('brochure_model');
        $this->load->model('tutorials_videos_model');
    }
    public function table($customer_id)
    {
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data('customer_media', ['customer_id' => $customer_id]);
        }
    }

    public function save()
    {
        if ($this->input->post()) {
            $data = $this->input->post();
            if ($data['id'] != '') {
                if (!has_permission('customer_media', '', 'edit')) {
                    access_denied('customer_media');
                }
                $exists = $this->customer_media_model->exists($data['customer_id'], $data['rel_type'], $data['rel_id'], $data['id']);
                if ($exists) {
                    set_alert('warning', 'Media already exists.');
                    redirect(admin_url('clients/client/' . $data['customer_id'] . '?group=customer_media'));
                }
                $this->customer_media_model->update($data['id'], $data);
                set_alert('success', 'Media updated successfully.');
            } else {
                if (!has_permission('customer_media', '', 'create')) {
                    access_denied('customer_media');
                }
                $exists = $this->customer_media_model->exists($data['customer_id'], $data['rel_type'], $data['rel_id']);
                if ($exists) {
                    set_alert('warning', 'Media already exists.');
                    redirect(admin_url('clients/client/' . $data['customer_id'] . '?group=customer_media'));
                }
                $this->customer_media_model->add($data);
                set_alert('success', 'Media added successfully.');
            }
        }
        redirect(admin_url('clients/client/' . $data['customer_id'] . '?group=customer_media'));
    }

    public function delete($id)
    {
        if (!has_permission('customer_media', '', 'delete')) {
            access_denied('customer_media');
        }
        if (!$id) {
            access_denied('customer_media');
        }
        $getdata = $this->customer_media_model->get($id);
        $response = $this->customer_media_model->delete($id);
        if ($response) {
            set_alert('success', 'Media deleted successfully.');
        } else {
            set_alert('warning', 'Error deleting media.');
        }
        redirect(admin_url('clients/client/' . $getdata['customer_id'] . '?group=customer_media'));
    }

    public function get_media_by_type()
    {
        $data = $this->input->post();
        if (isset($data['type'])) {
            $getData = [];
            if ($data['type'] == 'product_presentation') {
                $getData =  $this->product_presentation_model->get($data['form_id']);
            } elseif ($data['type'] == 'brochure') {
                $getData =  $this->brochure_model->get($data['form_id']);
            } elseif ($data['type'] == 'tutorial') {
                $getData =  $this->tutorials_videos_model->get($data['form_id']);
            }
            if (!empty($getData)) {
                $result['success'] = true;
                $result['data'] = $getData;
            } else {
                $result['success'] = false;
                $result['message'] = "Data not available";
            }
        } else {
            $result['success'] = false;
            $result['message'] = "Invalid request.";
        }
        echo json_encode($result);
    }
}
