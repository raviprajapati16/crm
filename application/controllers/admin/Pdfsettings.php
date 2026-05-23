<?php
class Pdfsettings extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('pdfsettings_model');
    }

    public function index($id = '')
    {
        if (!has_permission('pdf_settings', '', 'view')) {
            access_denied('pdf_settings');
        }
        if ($this->input->post() && !empty($id)) {
            $data = $this->input->post();
            $data['header'] = $this->input->post('header', FALSE);
            if (!has_permission('pdf_settings', '', 'edit')) {
                access_denied('pdf_settings');
            }
            $oldSetting = $this->pdfsettings_model->get($id);
            $path =  "uploads/pdf_settings/$id/";
            if ($data['watermark_type'] == "image") {
                if (isset($_FILES['file']['name']) && $_FILES['file']['name'] != '') {
                    $tmpFilePath = $_FILES['file']['tmp_name'];
                    if (!empty($tmpFilePath) && $tmpFilePath != '') {
                        _maybe_create_upload_path($path);
                        $filename    = unique_filename($path, $_FILES['file']['name']);
                        $newFilePath = $path . $filename;
                        if (move_uploaded_file($tmpFilePath, $newFilePath)) {
                            $data['watermark'] = $filename;
                            if (file_exists($path . $oldSetting->watermark)) {
                                unlink($path . $oldSetting->watermark);
                            }
                        }
                    }
                } else {
                    $data['watermark'] = $oldSetting->watermark;
                }
            }
            if ($data['watermark_type'] == "no") {
                $data['watermark'] = null;
                if (file_exists($path . $oldSetting->watermark)) {
                    unlink($path . $oldSetting->watermark);
                }
            }
            $success = $this->pdfsettings_model->update($data, $id);
            if ($success) {
                set_alert('success', 'PDF settings updated successfully');
            }
            redirect(admin_url('pdfsettings'));
        }

        if (!empty($id)) {
            $data['setting'] = $this->pdfsettings_model->get($id);
            if ($data['setting']) {
                return $this->load->view('admin/pdf_settings/edit', $data);
            } else {
                set_alert('danger', 'Error : something went wrong.');
                redirect(admin_url('pdfsettings'));
            }
        }
        $data['settings'] = $this->pdfsettings_model->get();
        return $this->load->view('admin/pdf_settings/list', $data);
    }
}
