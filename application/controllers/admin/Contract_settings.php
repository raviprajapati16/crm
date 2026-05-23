<?php
class Contract_settings extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('purchase_model');
    }


    public function index()
    {
        if (!is_admin()) {
            access_denied('contract_settings');
        }
        $this->load->view('admin/contract_settings/manage');
    }

    public function save()
    {
        if (!is_admin()) {
            access_denied('contract_settings');
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
            log_activity('Agreement Settings Updated');
            set_alert('success', "Agreement Settings updated successfully");
        } else {
            set_alert('danger', 'Some settings could not be saved. Please try again.');
        }

        redirect(admin_url('contract_settings'));
    }
}
