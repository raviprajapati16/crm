<?php
class Invoice_settings extends AdminController
{
    public function __construct()
    {
        parent::__construct();
    }


    public function index()
    {
        if (!is_admin()) {
            access_denied('invoice_settings');
        }
        $this->load->view('admin/invoice_settings/manage');
    }


    public function save()
    {
        if (!is_admin()) {
            access_denied('invoice_settings');
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
            set_alert('success', "Updated successfully");
        } else {
            set_alert('danger', 'Some settings could not be saved. Please try again.');
        }

        redirect(admin_url('invoice_settings'));
    }
}
