<?php
defined('BASEPATH') or exit('No direct script access allowed');

use SpreadsheetReader;

set_time_limit(0);

class Email_campaign_mail_list extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('email_campaign_mail_list_model');
    }


    public function index()
    {
        if (!has_permission('email_campaigns', '', 'view') && !has_permission('email_campaigns', '', 'view_own')) {
            access_denied('email_campaigns');
        }
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data('email_campaign_mail_list');
        }
        $this->load->view('admin/email_campaign_mail_list/manage');
    }


    public function save()
    {
        if (!has_permission('email_campaigns', '', 'create')) {
            access_denied('email_campaigns');
        }

        $data = $this->input->post();
        if (empty($data['id'])) {
            if (isset($_FILES['file']) && $_FILES['file']['error'] == UPLOAD_ERR_OK) {
                $filename = uniqid() . '_' . $_FILES['file']['name'];
                $temp_url = TEMP_FOLDER . $filename;

                $valid_emails = [];
                $total_invalid_address = 0;

                if (move_uploaded_file($_FILES['file']['tmp_name'], $temp_url)) {
                    try {
                        $fileType = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);

                        if ($fileType === 'csv') {
                            $handle = fopen($temp_url, 'r');
                            fgetcsv($handle); // Skip header
                            while (($row = fgetcsv($handle, 1000, ',')) !== false) {
                                if (!empty($row[1])) {
                                    $email = trim($row[1]);
                                    $name = trim($row[0]);
                                    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                                        $total_invalid_address++;
                                        continue;
                                    }
                                    $valid_emails[] = ['email' => $email, 'name' => $name];
                                }
                            }
                            fclose($handle);
                        } else {
                            $reader = new SpreadsheetReader($temp_url);
                            $sheets = $reader->Sheets();
                            $reader->ChangeSheet(0);
                            $firstRow = true;
                            foreach ($reader as $row) {
                                if ($firstRow) {
                                    $firstRow = false;
                                    continue; // Skip header row
                                }
                                if (!empty($row[1])) {
                                    $email = trim($row[1]);
                                    $name = trim($row[0]);
                                    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                                        $total_invalid_address++;
                                        continue;
                                    }
                                    $valid_emails[] = ['email' => $email, 'name' => $name];
                                }
                            }
                        }

                        if (!empty($valid_emails)) {
                            $uniqueEmails = [];
                            $valid_emails = array_filter($valid_emails, function ($item) use (&$uniqueEmails) {
                                if (in_array(strtolower($item['email']), $uniqueEmails)) {
                                    return false;
                                }
                                $uniqueEmails[] = strtolower($item['email']);
                                return true;
                            });
                            $valid_emails = array_values($valid_emails);
                        }

                        if (!empty($valid_emails)) {
                            $listArr = [
                                "title" => $data['title'],
                                "created_by" => get_staff_user_id(),
                                "created_at" => date('Y-m-d H:i:s'),
                            ];
                            $list_id = $this->email_campaign_mail_list_model->insert_list($listArr);
                            if ($list_id) {
                                log_activity("Email Campaign New Email List Created. ID [$list_id]");
                                foreach ($valid_emails as &$email_data) {
                                    $email_data['list_id'] = $list_id;
                                }
                                $this->email_campaign_mail_list_model->insert_emails_batch($valid_emails);
                                set_alert('success', "Email list created successfully");
                            } else {
                                set_alert('danger', 'Error: List not created.');
                            }
                        } else {
                            set_alert('danger', 'Error: No valid emails found.');
                        }
                    } catch (Exception $e) {
                        set_alert('danger', 'Error reading the file: ' . $e->getMessage());
                    }
                    unlink($temp_url);
                } else {
                    set_alert('danger', "Error: uploading file.");
                }
            } else {
                set_alert('danger', "Error: file not uploaded");
            }
        } else {
            $id = $data['id'];
            $listArr = [
                "title" => $data['title'],
            ];
            $check = $this->email_campaign_mail_list_model->update_list($id, $listArr);
            log_activity("Email Campaign Email List Updated. ID [$id]");
            set_alert('success', "Email list successfully updated");
        }
        redirect(admin_url('email_campaign_mail_list'));
    }



    public function delete($id)
    {
        if (!has_permission('email_campaigns', '', 'delete')) {
            access_denied('email_campaigns');
        }
        if (!$id) {
            redirect(admin_url('email_campaign_mail_list'));
        }
        $success = $this->email_campaign_mail_list_model->delete_mail_list($id);
        if ($success) {
            set_alert('success', "Email list delete successfully.");
        }
        redirect(admin_url('email_campaign_mail_list'));
    }

    public function mail_list_view($id)
    {
        if (!has_permission('email_campaigns', '', 'view') && !has_permission('email_campaigns', '', 'view_own')) {
            access_denied('email_campaigns');
        }
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data('email_campaign_mail_list_items', ["list_id" => $id]);
        }
        $this->load->view('admin/email_campaign_mail_list/manage_mail_list_items', ["list_id" => $id]);
    }

    public function save_item()
    {
        if (!has_permission('email_campaigns', '', 'create')) {
            access_denied('email_campaigns');
        }

        $data = $this->input->post();
        if (empty($data['id'])) {
            $listArr = array(
                "list_id" => $data['list_id'],
                "name" => $data['name'],
                "email" => $data['email'],
            );

            $duplicateCheck = $this->email_campaign_mail_list_model->is_duplicate_email($data['email'], $data['list_id']);
            if ($duplicateCheck > 0) {
                set_alert('danger', "Email already exists in this list.");
                redirect($_SERVER['HTTP_REFERER']);
            }

            $item_id = $this->email_campaign_mail_list_model->insert_email_item($listArr);
            if ($item_id) {
                log_activity("Email Campaign New Email List Item Created. Item ID [$item_id] List ID [" . $data['list_id'] . "]");
                set_alert('success', "Email created successfully");
            } else {
                set_alert('danger', 'Error : Email not created.');
            }
        } else {
            $id = $data['id'];
            $listArr = array(
                "name" => $data['name'],
                "email" => $data['email'],
            );

            $duplicateCheck = $this->email_campaign_mail_list_model->is_duplicate_email($data['email'], $data['list_id'], $id);
            if ($duplicateCheck > 0) {
                set_alert('danger', "Email already exists in this list.");
                redirect($_SERVER['HTTP_REFERER']);
            }
            $check = $this->email_campaign_mail_list_model->update_email_item($id, $listArr);
            log_activity("Email Campaign Email List Item Updated. Item ID [$id] List ID [" . $data['list_id'] . "]");
            set_alert('success', "Email successfully updated");
        }
        redirect($_SERVER['HTTP_REFERER']);
    }

    public function delete_item($id)
    {
        if (!has_permission('email_campaigns', '', 'delete')) {
            access_denied('email_campaigns');
        }
        if (!$id) {
            redirect($_SERVER['HTTP_REFERER']);
        }
        $success = $this->email_campaign_mail_list_model->delete_mail_list_item($id);
        if ($success) {
            log_activity("Email Campaign Email List Item Deleted. Item ID [$id]");
            set_alert('success', "Email delete successfully.");
        }
        redirect($_SERVER['HTTP_REFERER']);
    }
}
