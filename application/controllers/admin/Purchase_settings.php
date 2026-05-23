<?php
class Purchase_settings extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('purchase_model');
    }


    public function index()
    {
        if (!is_admin()) {
            access_denied('purchase_settings');
        }
        $data['statuses'] = $this->purchase_model->get_statuses();
        $this->load->view('admin/purchase_settings/manage', $data);
    }

    public function status()
    {
        if (!is_admin()) {
            access_denied('purchase_settings');
        }
        if ($this->input->post()) {
            $data = $this->input->post();
            if (!$this->input->post('id')) {
                $id = $this->purchase_model->add_status($data);
                if ($id) {
                    set_alert('success', "Status added successfully");
                }
            } else {
                $id = $data['id'];
                unset($data['id']);
                $success = $this->purchase_model->update_status($data, $id);
                if ($success) {
                    set_alert('success', "Status updated successfully");
                }
            }
        }
        redirect(admin_url('purchase_settings'));
    }

    public function delete_status($id)
    {
        if (!is_admin()) {
            access_denied('Leads Statuses');
        }
        if (!$id) {
            redirect(admin_url('purchase_settings'));
        }

        if (total_rows(db_prefix() . 'purchase', array('status' => $id, 'deleted_at' => null)) > 0) {
            set_alert('warning', "Status could not be deleted. There are some purchases with this status");
            redirect(admin_url('purchase_settings'));
        }

        $response = $this->purchase_model->delete_status($id);
        if ($response) {
            set_alert('success', "Status deleted successfully");
        } else {
            set_alert('warning', "Status could not be deleted");
        }
        redirect(admin_url('purchase_settings'));
    }

    public function save_terms()
    {
        if (!is_admin()) {
            access_denied('purchase_settings');
        }

        $data = $this->input->post(null, false);

        $success_count = 0;
        $error_count = 0;

        foreach ($data as $name => $value) {
            $existing = $this->db->get_where('tbloptions', array('name' => $name))->row();
            if ($existing) {
                $update_data = array(
                    'value' => $value,
                    'autoload' => 1
                );

                $this->db->where('name', $name);
                if ($this->db->update(db_prefix() . 'options', $update_data)) {
                    $success_count++;
                } else {
                    $error_count++;
                }
            } else {
                $insert_data = array(
                    'name' => $name,
                    'value' => $value,
                    'autoload' => 1
                );

                if ($this->db->insert(db_prefix() . 'options', $insert_data)) {
                    $success_count++;
                } else {
                    $error_count++;
                }
            }
        }

        if ($error_count == 0) {
            log_activity('Purchase Settings Updated');
            set_alert('success', "Purchase settings updated successfully");
        } else {
            set_alert('danger', 'Some settings could not be saved. Please try again.');
        }

        redirect(admin_url('purchase_settings'));
    }

    public function status_reorder()
    {
        if (!is_admin()) {
            $result['success'] = false;
            $result['message'] = "Access denied";
            echo json_encode($result);
            exit;
        }
        if ($this->input->post()) {
            $data = $this->input->post();
            $i = 1;
            foreach ($data['order'] as $id) {
                $this->purchase_model->update_status(["statusorder" => $i], $id);
                $i++;
            }
            $result['success'] = true;
            $result['message'] =  "Status re-ordered successfully";
        } else {
            $result['success'] = false;
            $result['message'] = "Invalid request";
        }
        echo json_encode($result);
    }
}
