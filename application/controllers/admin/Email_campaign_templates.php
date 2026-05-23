<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Email_campaign_templates extends AdminController
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('email_campaign_templates_model');
    }


    public function index()
    {
        if (!has_permission('email_campaigns', '', 'view') && !has_permission('email_campaigns', '', 'view_own')) {
            access_denied('email_campaigns_template');
        }
        $data['templates'] = $this->email_campaign_templates_model->get_all_templates();

        if ($this->input->post()) {
            $this->app->get_table_data('email_campaign_templates');
        }
        $this->load->view('admin/email_campaign_templates/index', $data);
    }


    public function template()
    {
        if (!has_permission('email_campaigns', '', 'view') && !has_permission('email_campaigns', '', 'view_own')) {
            access_denied('email_campaigns_template');
        }
        if ($this->input->post()) {
            $data = $this->input->post();

            if ($data['id'] == '') {
                if (!has_permission('email_campaigns', '', 'create')) {
                    access_denied('email_campaigns');
                }
                $id = $this->email_campaign_templates_model->save_template($data);
                if ($id) {
                    $template = $this->email_campaign_templates_model->get_template(1);
                    if ($template) {
                        $sourceDir = "uploads/email_campaign_templates/1";
                        $destinationDir = "uploads/email_campaign_templates/$id";
                        copyFolder($sourceDir, $destinationDir);
                    }
                    log_activity("Email campaign template created. ID [$id]");
                    set_alert('success', 'Template created successfully');
                    redirect(admin_url('email_campaign_templates'));
                }
            } else {
                if (!has_permission('email_campaigns', '', 'edit')) {
                    access_denied('email_campaigns');
                }
                $success = $this->email_campaign_templates_model->update_template($data['id'], $data,);
                if ($success) {
                    log_activity("Email campaign template updated. ID [" . $data['id'] . "]");
                    set_alert('success', 'Template updated successfully');
                }
                redirect(admin_url('email_campaign_templates'));
            }
        }
    }

    public function delete($id)
    {
        if (!has_permission('email_campaigns', '', 'delete')) {
            access_denied('email_campaigns');
        }
        if ($id) {
            $success = $this->email_campaign_templates_model->delete_template($id);
            if ($success) {
                $folder = FCPATH . 'uploads/email_campaign_templates/' . $id;
                deleteFolder($folder);
                log_activity("Email campaign template deleted. ID [$id]");
                set_alert('success', 'Template deleted successfully');
            } else {
                set_alert('danger', 'Error: Template not deleted');
            }
            redirect(admin_url('email_campaign_templates'));
        } else {
            redirect(admin_url('email_campaign_templates'));
        }
    }

    public function edit($id)
    {
        if (!has_permission('email_campaigns', '', 'edit')) {
            access_denied('email_campaigns');
        }
        if ($id) {
            $data['template_data'] =  $this->email_campaign_templates_model->get_template($id);
            $this->load->view('admin/email_campaign_templates/editor', $data);
        } else {
            set_alert('danger', 'Invalid request.');
            redirect(admin_url('email_campaign_templates'));
        }
    }


    public function get_template()
    {
        if ($this->input->post()) {
            $data = $this->input->post();
            $template_data =  $this->email_campaign_templates_model->get_template($data['id']);
            if ($template_data) {
                $result['success'] = true;
                $result['data'] =  $template_data;
            } else {
                $result['success'] = false;
                $result['message'] = "Invalid template";
            }
        } else {
            $result['success'] = false;
            $result['message'] = "Invalid request";
        }
        echo json_encode($result);
    }

    public function duplicate($id)
    {
        if (!has_permission('email_campaigns', '', 'create')) {
            access_denied('email_campaigns');
        }

        $template = $this->email_campaign_templates_model->get_template($id);
        if ($template) {
            $new_data = [
                'subject' => "WRITE YOUR SUBJECT HERE",
                'title' => $template->title . " (Copy)",
            ];
            $new_id = $this->email_campaign_templates_model->save_template($new_data);

            $sourceDir = "uploads/email_campaign_templates/$id";
            $destinationDir = "uploads/email_campaign_templates/$new_id";
            copyFolder($sourceDir, $destinationDir);

            log_activity("Email campaign template duplicate created. From ID [$id] to ID [$new_id]");
            redirect('admin/email_campaign_templates/');
        }

        redirect('admin/email_campaign_templates');
    }

    public function save()
    {
        if (!has_permission('email_campaigns', '', 'create')) {
            access_denied('email_campaigns');
        }
        if (!isset($_FILES['template'])) {
            echo json_encode(['error' => 'No template file provided']);
            return;
        }
        $id = $this->input->post('id');
        $template_dir = FCPATH . 'uploads/email_campaign_templates/' . $id . '/';
        if (!file_exists($template_dir)) {
            mkdir($template_dir, 0777, true);
        }

        $filename = 'index.html';
        $filepath = $template_dir . $filename;

        $upload_path = FCPATH . "uploads/email_campaign_templates/" . $id . "/index.html";
        if (file_exists($upload_path)) {
            unlink($upload_path);
        }
        if (move_uploaded_file($_FILES['template']['tmp_name'], $filepath)) {
            $this->email_campaign_templates_model->update_template_timestamp($this->input->post('id'));
            log_activity("Email campaign template updated. ID [$id]");
            echo json_encode([
                'success' => true,
                'filename' => $filename
            ]);
        } else {
            echo json_encode(['error' => 'Failed to save template']);
        }
    }

    public function upload_image()
    {
        $id = $this->input->post('id');
        $config['upload_path'] = './uploads/email_campaign_templates/'.$id.'/';
        $config['allowed_types'] = 'gif|jpg|jpeg|png';
        $config['max_size'] = '2048';
        $config['encrypt_name'] = true;

        if (!file_exists($config['upload_path'])) {
            mkdir($config['upload_path'], 0777, true);
        }
        $this->load->library('upload', $config);
        $uploaded_files = [];
        $upload_errors = [];

        $files = $_FILES['files'];
        if (is_array($files['name'])) {
            foreach ($files['name'] as $key => $filename) {
                $_FILES['file']['name'] = $files['name'][$key];
                $_FILES['file']['type'] = $files['type'][$key];
                $_FILES['file']['tmp_name'] = $files['tmp_name'][$key];
                $_FILES['file']['error'] = $files['error'][$key];
                $_FILES['file']['size'] = $files['size'][$key];

                if ($this->upload->do_upload('file')) {
                    $uploaded_file = $this->upload->data();
                    $uploaded_files[] = base_url('uploads/email_campaign_templates/' . $id . '/' . $uploaded_file['file_name']);
                } else {
                    $upload_errors[] = $this->upload->display_errors('', '');
                }
            }
        }

        if (!empty($uploaded_files)) {
            echo json_encode([
                'success' => true,
                'urls' => $uploaded_files,
                'errors' => $upload_errors
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'No files were uploaded successfully',
                'errors' => $upload_errors
            ]);
        }
    }

    public function template_preview()
    {
        if (!has_permission('email_campaigns', '', 'view') && !has_permission('email_campaigns', '', 'view_own')) {
            ajax_access_denied('email_campaigns_template');
        }
        $data = $this->input->post();
        if (isset($data['id'])) {
            $template_file = FCPATH . 'uploads/email_campaign_templates/' . $data['id'] . "/index.html";
            if (file_exists($template_file)) {
                $result['success'] = true;
                $result['template_url'] = site_url('uploads/email_campaign_templates/' . $data['id'] . "/index.html?v=" . time());
            } else {
                $result['success'] = false;
                $result['message'] = "Error : Template file not exists on server.";
            }
        } else {
            $result['success'] = false;
            $result['message'] = "Invalid request.";
        }
        echo json_encode($result);
    }
}
