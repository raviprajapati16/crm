<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Download extends App_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('download');
    }

    public function preview_video()
    {
        $path      = FCPATH . $this->input->get('path');
        $file_type = $this->input->get('type');

        $allowed_extensions = get_html5_video_extensions();

        $pathinfo = pathinfo($path);

        if (!file_exists($path) || !isset($pathinfo['extension']) || !in_array($pathinfo['extension'], $allowed_extensions)) {
            $file_type = 'image/jpg';
            $path      = FCPATH . 'assets/images/preview-not-available.jpg';
        }

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($path) . '"');
        header('Content-Type: ' . $file_type);
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');

        if (ob_get_contents()) {
            ob_end_clean();
        }

        hooks()->do_action('before_output_preview_video');

        $file = fopen($path, 'rb');
        if ($file !== false) {
            while (!feof($file)) {
                echo fread($file, 1024);
            }
            fclose($file);
        }
    }

    public function preview_image()
    {
        $path      = FCPATH . $this->input->get('path');
        $file_type = $this->input->get('type');

        $allowed_extensions = [
            'jpg',
            'jpeg',
            'png',
            'bmp',
            'gif',
            'tif',
        ];

        $pathinfo = pathinfo($path);

        if (!file_exists($path) || !isset($pathinfo['extension']) || !in_array($pathinfo['extension'], $allowed_extensions)) {
            $file_type = 'image/jpg';
            $path      = FCPATH . 'assets/images/preview-not-available.jpg';
        }

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($path) . '"');
        header('Content-Type: ' . $file_type);
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');

        if (ob_get_contents()) {
            ob_end_clean();
        }

        hooks()->do_action('before_output_preview_image');
        $file = fopen($path, 'rb');
        if ($file !== false) {
            while (!feof($file)) {
                echo fread($file, 1024);
            }
            fclose($file);
        }
    }

    public function file($folder_indicator, $attachmentid = '', $extra_param = "")
    {
        $this->load->model('tickets_model');
        if ($folder_indicator == 'ticket') {
            if (is_logged_in()) {
                $this->db->where('id', $attachmentid);
                $attachment = $this->db->get(db_prefix() . 'ticket_attachments')->row();
                if (!$attachment) {
                    show_404();
                }
                $ticket   = $this->tickets_model->get_ticket_by_id($attachment->ticketid);
                $ticketid = $attachment->ticketid;
                if ($ticket->userid == get_client_user_id() || is_staff_logged_in()) {
                    if ($attachment->id != $attachmentid) {
                        show_404();
                    }
                    $path = get_upload_path_by_type('ticket') . $ticketid . '/' . $attachment->file_name;
                }
            }
        } elseif ($folder_indicator == 'newsfeed') {
            if (is_staff_logged_in()) {
                if (!$attachmentid) {
                    show_404();
                }
                $this->db->where('id', $attachmentid);
                $attachment = $this->db->get(db_prefix() . 'files')->row();
                if (!$attachment) {
                    show_404();
                }
                $path = get_upload_path_by_type('newsfeed') . $attachment->rel_id . '/' . $attachment->file_name;
            }
        } elseif ($folder_indicator == 'contract') {
            if (!$attachmentid) {
                show_404();
            }

            $this->db->where('attachment_key', $attachmentid);
            $attachment = $this->db->get(db_prefix() . 'files')->row();
            if (!$attachment) {
                show_404();
            }

            if (!is_staff_logged_in()) {
                $this->db->select('not_visible_to_client');
                $this->db->where('id', $attachment->rel_id);
                $contract = $this->db->get(db_prefix() . 'contracts')->row();
                if ($contract->not_visible_to_client == 1) {
                    show_404();
                }
            }

            $path = get_upload_path_by_type('contract') . $attachment->rel_id . '/' . $attachment->file_name;
        } elseif ($folder_indicator == 'taskattachment') {
            if (!is_logged_in()) {
                show_404();
            }

            $this->db->where('attachment_key', $attachmentid);
            $attachment = $this->db->get(db_prefix() . 'files')->row();

            if (!$attachment) {
                show_404();
            }
            $path = get_upload_path_by_type('task') . $attachment->rel_id . '/' . $attachment->file_name;
        } elseif ($folder_indicator == 'sales_attachment') {
            if (strpos($attachmentid, 'project') !== false) {
                $attachmentid = preg_replace('/\D/', '', $attachmentid);
                $this->db->select('id,file_name,filetype,project_id');
                $this->db->where('id', $attachmentid);
                $fileData = $this->db->get(db_prefix() . 'project_files')->row_array();
                if (!$fileData) {
                    show_404();
                }
                if (!empty($fileData['file_name'])) {
                    $path = get_upload_path_by_type('project') . $fileData['project_id'] . "/" . $fileData['file_name'];
                } else {
                    show_404();
                }
            } else {
                if (!is_staff_logged_in()) {
                    $this->db->where('visible_to_customer', 1);
                }
                $this->db->where('attachment_key', $attachmentid);
                $attachment = $this->db->get(db_prefix() . 'files')->row();
                if (!$attachment) {
                    show_404();
                }
                $path = get_upload_path_by_type($attachment->rel_type) . $attachment->rel_id . '/' . $attachment->file_name;
            }
        } elseif ($folder_indicator == 'expense') {
            if (!is_staff_logged_in()) {
                show_404();
            }
            $this->db->where('rel_id', $attachmentid);
            $this->db->where('rel_type', 'expense');
            $file = $this->db->get(db_prefix() . 'files')->row();
            $path = get_upload_path_by_type('expense') . $file->rel_id . '/' . $file->file_name;
            // l_attachment_key is if request is coming from public form
        } elseif ($folder_indicator == 'lead_attachment' || $folder_indicator == 'l_attachment_key') {
            if (!is_staff_logged_in() && strpos($_SERVER['HTTP_REFERER'], 'forms/l/') === false) {
                show_404();
            }

            // admin area
            if ($folder_indicator == 'lead_attachment') {
                $this->db->where('id', $attachmentid);
            } else {
                // Lead public form
                $this->db->where('attachment_key', $attachmentid);
            }

            $attachment = $this->db->get(db_prefix() . 'files')->row();

            if (!$attachment) {
                show_404();
            }

            $path = get_upload_path_by_type('lead') . $attachment->rel_id . '/' . $attachment->file_name;
        } elseif ($folder_indicator == 'client') {
            $this->db->where('attachment_key', $attachmentid);
            $attachment = $this->db->get(db_prefix() . 'files')->row();
            if (!$attachment) {
                show_404();
            }
            if (has_permission('customers', '', 'view') || is_customer_admin($attachment->rel_id) || is_client_logged_in()) {
                $path = get_upload_path_by_type('customer') . $attachment->rel_id . '/' . $attachment->file_name;
            }
        } elseif ($folder_indicator == 'vendor_quotation_files') {
            $this->db->select('id, lead_id, file');
            $this->db->where('id', $attachmentid);
            $formData = $this->db->get(db_prefix() . 'vendor_quoation_forms')->row();
            if (!$formData) {
                show_404();
            }
            if (!empty($formData->file)) {
                $path = get_upload_path_by_type('lead') . $formData->lead_id . "/" . $formData->file;
            } else {
                show_404();
            }
        } elseif ($folder_indicator == 'lead_inquiry_form_files') {
            $this->db->select('type, answer, form_id');
            $this->db->where('id', $attachmentid);
            $formData = $this->db->get(db_prefix() . 'lead_inquiry_forms_data')->row();
            if (!$formData) {
                show_404();
            }
            $this->db->where('id', $formData->form_id);
            $form = $this->db->get(db_prefix() . 'lead_inquiry_forms')->row();
            if ($formData->type == 'fileupload' && !empty($formData->answer)) {
                $path = get_upload_path_by_type('lead') . $form->lead_id . "/" . $formData->answer;
            } else {
                show_404();
            }
        } elseif ($folder_indicator == 'lead_plan_visit_form_files' && !empty($extra_param)) {
            $this->db->select('lead_id, photo, aadhar_card, pan_card, signature');
            $this->db->where('id', $attachmentid);
            $formData = $this->db->get(db_prefix() . 'plant_visit_forms')->row_array();
            if (!$formData) {
                show_404();
            }
            if (isset($formData[$extra_param]) && !empty($formData[$extra_param])) {
                $path = get_upload_path_by_type('lead') . $formData['lead_id'] . "/" . $formData[$extra_param];
            } else {
                show_404();
            }
        } elseif ($folder_indicator == 'project') {
            $this->db->select('id,file_name,filetype,project_id');
            $this->db->where('id', $attachmentid);
            $fileData = $this->db->get(db_prefix() . 'project_files')->row_array();
            if (!$fileData) {
                show_404();
            }
            if (!empty($fileData['file_name'])) {
                $path = get_upload_path_by_type('project') . $fileData['project_id'] . "/" . $fileData['file_name'];
            } else {
                show_404();
            }
        } elseif ($folder_indicator == 'hrm_staff_file') {
            if (!$attachmentid) {
                show_404();
            }
            $this->db->where('id', $attachmentid);
            $attachment = $this->db->get(db_prefix() . 'files')->row();
            if (!$attachment) {
                show_404();
            }
            $path = get_upload_path_by_type('hrm_staff_file') . $attachment->rel_id . '/' . $attachment->file_name;
        } else {
            die('folder not specified');
        }

        if (!file_exists($path)) {
            show_404();
        }

        $file_content = file_get_contents($path);
        $file_type = mime_content_type($path);
        $file_name = basename($path);

        $data = [
            'file_content' => base64_encode($file_content),
            'file_type' => $file_type,
            'file_name' => $file_name,
            'path' => $path
        ];
        $this->load->view('themes/perfex/file-viewer', $data);
        //force_download($path, null);
    }

    public function file_download()
    {
        $path = $this->input->get('path');
        if (!file_exists($path)) {
            show_404();
        }

        $file_content = file_get_contents($path);
        $file_type = mime_content_type($path);
        $file_name = basename($path);

        $data = [
            'file_content' => base64_encode($file_content),
            'file_type' => $file_type,
            'file_name' => $file_name,
            'path' => $path
        ];

        $this->load->view('themes/perfex/file-viewer', $data);
        //force_download($path, null);
    }

    public function doc_download()
    {
        $path = $this->input->get('path');
        if (!file_exists($path)) {
            show_404();
        }
        force_download($path, null);
    }
}
