<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Cron extends App_Controller
{
    public function index($key = '')
    {
        update_option('cron_has_run_from_cli', 1);

        if (defined('APP_CRON_KEY') && (APP_CRON_KEY != $key)) {
            header('HTTP/1.0 401 Unauthorized');
            die('Passed cron job key is not correct. The cron job key should be the same like the one defined in APP_CRON_KEY constant.');
        }

        $last_cron_run = get_option('last_cron_run');
        $seconds = hooks()->apply_filters('cron_functions_execute_seconds', 300);

        if ($last_cron_run == '' || (time() > ($last_cron_run + $seconds))) {
            $this->load->model('cron_model');
            $this->cron_model->run();
        }
    }

    public function updateleads()
    {
        set_time_limit(0);
        ini_set("memory_limit", -1);
        $CI =  &get_instance();
        $CI->db->truncate('tblfilteredleads');
        log_activity('updateleads table truncate successful.');
        $CI->db->query("insert into tblfilteredleads SELECT * FROM tblleadsview;");
        log_activity('updateleads table insert successful.');
    }

    public function deletedblanktags()
    {
        $CI = &get_instance();
        $query = $CI->db->select('tbltags.*')
            ->from('tbltags')
            ->join('tbltaggables', 'tbltags.id = tbltaggables.tag_id', 'left')
            ->where('tbltaggables.rel_id IS NULL')
            ->get();
        // Check if there are any rows fetched
        if ($query->num_rows() > 0) {
            $ids = array_column($query->result_array(), 'id');
            // Delete the fetched rows
            $CI->db->where_in('id', $ids);
            $CI->db->delete('tbltags');
            log_message('info', $query->num_rows(), 'Empty tags removed.');
        } else {
            log_message('info', ' No Empty tags found.');
        }
    }

    public function make_tags_unique()
    {
        $this->db->trans_start();
        $duplicate_tags_query = $this->db->query("
            SELECT name, COUNT(*) AS duplicate_count
            FROM tbltags
            GROUP BY name
            HAVING COUNT(*) > 1
        ");

        $duplicate_tags = $duplicate_tags_query->result_array();
        foreach ($duplicate_tags as $tag) {
            $tag_name = $tag['name'];
            $tags_with_same_name = $this->db->select('id')
                ->from('tbltags')
                ->where('name', $tag_name)
                ->get()
                ->result_array();
            $first_tag_id = $tags_with_same_name[0]['id'];
            for ($i = 1; $i < count($tags_with_same_name); $i++) {
                $duplicate_tag_id = $tags_with_same_name[$i]['id'];
                $this->db->where('tag_id', $duplicate_tag_id);
                $this->db->update('tbltaggables', ['tag_id' => $first_tag_id]);
                $this->db->where('id', $duplicate_tag_id);
                $this->db->delete('tbltags');
            }
        }
        $this->db->trans_complete();
        if ($this->db->trans_status() === FALSE) {
            log_activity('Cron Job Error :products (tags) unique cron job error.');
        } else {
            log_activity('Cron Job : make products (tags) unique successfully run.');
        }
    }



    public function reminder()
    {
        $this->load->model('cron_model');
        $this->cron_model->staff_reminders();
    }

    public function delete_email_temp_files()
    {
        $this->load->helper('file');
        $folder_path = 'uploads/temp_mail_attachments';
        $now = time();

        function deleteOldFilesAndFolders($dir, $timeLimit, $now)
        {
            $items = scandir($dir);
            foreach ($items as $item) {
                if ($item === '.' || $item === '..') {
                    continue;
                }
                $item_path = $dir . '/' . $item;
                $item_mod_time = filemtime($item_path);
                $item_age = $now - $item_mod_time;
                if ($dir === 'uploads/temp_mail_attachments' && ($item === '.htaccess' || $item === 'index.html')) {
                    continue;
                }
                if (is_dir($item_path)) {
                    deleteOldFilesAndFolders($item_path, $timeLimit, $now);
                    if (count(scandir($item_path)) == 2) {
                        rmdir($item_path);
                    }
                } else {
                    if ($item_age > $timeLimit) {
                        unlink($item_path);
                    }
                }
            }
        }
        $timeLimit = 86400;
        deleteOldFilesAndFolders($folder_path, $timeLimit, $now);
        echo "24 hours old files and folders deleted successfully.";
    }


    public function download_request_reset()
    {
        $this->load->model('cron_model');
        $getProposaData = $this->cron_model->get_table_data(["download_request" => "1"], "proposals");
        if (!empty($getProposaData)) {
            foreach ($getProposaData as $key => $item) {
                if (!empty($item['download_allow_till']) && strtotime(date('Y-m-d', strtotime($item['download_allow_till']))) < strtotime(date('Y-m-d'))) {
                    $this->cron_model->update_table_data(["download_request" => "0", "download_allow_till" => NULL], $item['id'], "proposals");
                }
            }
        }
    }

    public function fetch_biomatric_attendance()
    {
        $this->load->model('biomatric_model');
        $json_data = file_get_contents('php://input');
        $records = $this->biomatric_model->insert_attendance($json_data);
        if ($records > 0) {
            $message = 'Bio-Matric Cron JOB : Successfully fetched the data and inserted ' . $records . ' Records.';
            log_activity($message);
            echo $message;
            exit;
        }
    }

    public function contract_payment_reminder_email_send()
    {
        set_time_limit(0);
        $this->load->model('contracts_model');
        $this->contracts_model->send_contract_payment_term_reminder_email();
        $this->contracts_model->send_contract_payment_over_due_notice_email();
    }

    public function goals_notification_send_cron_job()
    {
        set_time_limit(0);
        goals_notification();
        goals_emails();
    }


    public function email_campaigns()
    {
        date_default_timezone_set('Asia/Kolkata');
        $this->load->model('email_campaigns_model');
        $isOn = (get_option('email_campaign_operation_hours') == "1") ? true : false;
        if ($isOn) {
            $startTime = (get_option('email_campaigns_start_time')) ? get_option('email_campaigns_start_time') : "10:00";
            $endTime = (get_option('email_campaigns_end_time')) ? get_option('email_campaigns_end_time') : "18:00";
            $currentTime = date('H:i');
            if ($currentTime < $startTime || $currentTime >= $endTime) {
                $campaign = $this->db->select('*')
                    ->from(db_prefix() . 'emailcampaign')
                    ->where('start_date <=', date('Y-m-d H:i:s'))
                    ->where('status', 'In Progress')
                    ->order_by('id', 'ASC')
                    ->limit(1)
                    ->get()
                    ->row();

                if (!empty($campaign)) {
                    campaign_update(
                        [
                            "status" => "Paused",
                            "status_message" => "Email campaign paused due to operating hours limitation from $startTime to $endTime"
                        ],
                        $campaign->id
                    );
                }
                return false;
            }
        }

        log_activity("Email campaign cron run.");
        //Resume paused campaign.
        $campaigns = $this->db->select('*')
            ->from(db_prefix() . 'emailcampaign')
            ->where('start_date <=', date('Y-m-d H:i:s'))
            ->where('status', 'Paused')
            ->get()->result();
        if (!empty($campaigns)) {
            foreach ($campaigns as $key => $campaign) {
                $lastSuccessEmail = $this->db->from(db_prefix() . 'emailcampaign_queue')
                    ->where('status !=', 'queue')
                    ->where('campaign_id', $campaign->id)
                    ->order_by('email_sent_at', 'DESC')
                    ->limit(1)
                    ->get()->row();
                if (!empty($lastSuccessEmail)) {
                    if (date('Y-m-d', strtotime($lastSuccessEmail->email_sent_at)) != date('Y-m-d')) {
                        $this->db->where('id', $campaign->id);
                        $this->db->update(db_prefix() . 'emailcampaign', ["status" => "In Queue", "status_message" => "Campaign is in queue."]);
                    }
                }
            }
        }

        $campaign = $this->db->select('*')
            ->from(db_prefix() . 'emailcampaign')
            ->where('start_date <=', date('Y-m-d H:i:s'))
            ->where_not_in('status', ['Completed', 'Stopped', 'Paused'])
            ->order_by('id', 'ASC')
            ->limit(1)
            ->get()->row();

        if (!empty($campaign)) {

            // All other campaign set to in queue if is it in progress.
            $this->db->where('id !=', $campaign->id);
            $this->db->where('status', "In Progress");
            $this->db->update(db_prefix() . 'emailcampaign', ["status" => "In Queue", "status_message" => "In Queue"]);

            $template =  $this->db->select('*')
                ->from(db_prefix() . 'emailcampaign_templates')
                ->where('id', $campaign->template_id)
                ->get()->row();
            $mailBody = "";
            $subject = "";
            if (!empty($template)) {
                $subject = $template->subject;
                $template_file = FCPATH . 'uploads/email_campaign_templates/' . $template->id . "/index.html";
                if (file_exists($template_file)) {
                    $mailBody = file_get_contents($template_file) . "{track_link}";
                    $mailBody = str_replace("../../../builderjs", site_url('/builderjs'), $mailBody);
                    if (!$mailBody) {
                        campaign_update(["status" => "Error",  "status_message" => "Template file convert failed."], $campaign->id);
                        exit;
                    }
                } else {
                    campaign_update(["status" => "Error",  "status_message" => "Template file not exists on server."], $campaign->id);
                    exit;
                }
            } else {
                campaign_update(["status" => "Error",  "status_message" => "Template not found."], $campaign->id);
                exit;
            }

            if (empty($subject)) {
                campaign_update(["status" => "Error",  "status_message" => "Email Template subject is empty."], $campaign->id);
                exit;
            }

            if (empty($mailBody)) {
                campaign_update(["status" => "Error",  "status_message" => "Email body is not available."], $campaign->id);
                exit;
            }

            $queueEmails = $this->db->from(db_prefix() . 'emailcampaign_queue')
                ->where('status', 'queue')
                ->where('campaign_id', $campaign->id)
                ->limit(40)
                ->get()->result();

            if (!empty($queueEmails)) {
                $this->db->where('id', $campaign->id);
                $this->db->update(db_prefix() . 'emailcampaign', ["status" => "In Progress", "status_message" => "Email campaign is running."]);
                foreach ($queueEmails as $key => $item) {

                    //Limit Reached
                    $limitReached = $this->email_campaigns_model->is_limit_reached($campaign->id, $item->id);
                    if (isset($limitReached['limit_reached'])) {
                        continue;
                    }
                    if (isset($limitReached['campaign_paused'])) {
                        campaign_update(["status" => "Paused",  "status_message" => "Paused due to daily mail limit reached."], $campaign->id);
                        break;
                    }

                    $trackUrl = site_url('email_track/update/email_campaign/' . $item->hash);
                    $replacements = [
                        'EMAIL_RECIPIENT_NAME' => $item->name,
                        '{track_link}' => '<img src="' . $trackUrl . '" alt="" width="1" height="1" border="0" />',
                    ];
                    $pattern = "/\*\|.*?\|\*/";
                    $mailBody = preg_replace($pattern, '', $mailBody);
                    $mailBody = str_replace(array_keys($replacements), array_values($replacements), $mailBody);

                    $smtpSettings = [];
                    if ($item->send_from == "staff") {
                        $staffData = get_staff($item->mail_send_from_id);
                        if (isset($staffData) && !empty($staffData->mail_service)) {
                            $smtpService = get_mail_service_data($staffData->mail_service);
                            if (!empty($smtpService)) {
                                $smtpSettings['from'] = get_option('brandname');
                                $smtpSettings['email'] = $staffData->webmail_email;
                                $smtpSettings['password'] = $staffData->webmail_password;
                                $smtpSettings['smtp_host'] = $smtpService->smtp_host;
                                $smtpSettings['smtp_encryption'] = $smtpService->smtp_encryption;
                                $smtpSettings['smtp_port'] = $smtpService->smtp_port;
                                $smtpSettings['charset'] = $smtpService->email_charset;
                            }
                        }
                    } else if ($item->send_from == "custom_email") {
                        $customMail =  $this->db->select('*')
                            ->from(db_prefix() . 'emailcampaign_emails')
                            ->where('id', $item->mail_send_from_id)
                            ->get()->row();

                        if (isset($customMail) && !empty($customMail->service_id)) {
                            $smtpService = get_mail_service_data($customMail->service_id);
                            if (!empty($smtpService)) {
                                $smtpSettings['from'] = get_option('brandname');
                                $smtpSettings['email'] = $customMail->email;
                                $smtpSettings['password'] = $customMail->password;
                                $smtpSettings['smtp_host'] = $smtpService->smtp_host;
                                $smtpSettings['smtp_encryption'] = $smtpService->smtp_encryption;
                                $smtpSettings['smtp_port'] = $smtpService->smtp_port;
                                $smtpSettings['charset'] = $smtpService->email_charset;
                            }
                        }
                    }

                    $mailArr = $smtpSettings;
                    $mailArr['to'] = $item->email;
                    $mailArr['subject'] = $subject;
                    $mailArr['email_body'] = $mailBody;
                    $mailArr['reply_to'] = array_filter(explode(",", $campaign->reply_to));
                    $this->load->library('app_email_campaign');
                    $emailStatus = $this->app_email_campaign->send_email($mailArr);
                    if (isset($emailStatus['success']) && isset($emailStatus['message'])) {
                        $status = $emailStatus['success'];
                        $message = $emailStatus['message'];
                    } else {
                        $status = "failed";
                        $message = "Unknown error";
                    }

                    $this->email_campaigns_model->updateQueue($item->id, [
                        "status" => $status,
                        "status_message" => $message,
                        "email_sent_at" => date('Y-m-d H:i:s')
                    ]);
                }

                $queueEmailsCount = $this->db->from(db_prefix() . 'emailcampaign_queue')
                    ->where('status', 'queue')
                    ->where('campaign_id', $campaign->id)
                    ->count_all_results();
                if ($queueEmailsCount == 0) {
                    $this->db->where('id', $campaign->id);
                    $this->db->update(db_prefix() . 'emailcampaign', ["status" => "Completed", "status_message" => "Campaign Successfully completed."]);
                }
            } else {
                campaign_update(["status" => "Paused",  "status_message" => "Email not availble in queue for this campaign."], $campaign->id);
                exit;
            }
        }
    }

    public function remove_wrong_emails_from_leads()
    {
        set_time_limit(0);
        $this->load->database();
        $this->load->helper('file');

        $csvFile = './uploads/emails.csv';

        if (!file_exists($csvFile)) {
            echo "CSV file not found.";
            return;
        }

        // Read CSV and get email list
        $emails = [];
        if (($handle = fopen($csvFile, 'r')) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $emails[] = trim($data[0]);
            }
            fclose($handle);
        }

        if (empty($emails)) {
            echo "No emails found in the CSV file.";
            return;
        }


        // Chunk emails to handle large datasets and reduce memory usage
        $chunkSize = 100; // Adjust chunk size based on your database performance
        $deleted = [];
        $notMatched = [];

        foreach (array_chunk($emails, $chunkSize) as $chunk) {
            // Fetch leads matching the emails in the current chunk
            $this->db->where_in('email', $chunk);
            $query = $this->db->get(db_prefix() . 'leads');
            if ($query->num_rows() > 0) {
                $matchedEmails = [];
                foreach ($query->result() as $row) {
                    $matchedEmails[] = $row->email;
                    $deleted[] = $row->email;
                }
                $this->db->where_in('email', $matchedEmails);
                $this->db->update(db_prefix() . 'leads', ['email' => NULL]);
            }

            // customer contacts
            $this->db->where_in('email', $chunk);
            $query = $this->db->get(db_prefix() . 'contacts');
            if ($query->num_rows() > 0) {
                $matchedEmails = [];
                foreach ($query->result() as $row) {
                    $matchedEmails[] = $row->email;
                    $deleted[] = $row->email;
                }
                $this->db->where_in('email', $matchedEmails);
                $this->db->update(db_prefix() . 'contacts', ['email' => NULL]);
            }


            //email campaign Email lists
            $this->db->where_in('email', $chunk);
            $query = $this->db->get(db_prefix() . 'emailcampaign_mail_list_items');

            if ($query->num_rows() > 0) {
                $matchedEmails = [];
                foreach ($query->result() as $row) {
                    $matchedEmails[] = $row->email;
                    $deleted[] = $row->email;
                }

                // Delete records
                $this->db->where_in('email', $matchedEmails);
                $this->db->delete(db_prefix() . 'emailcampaign_mail_list_items');
            }


            //Email campaign Email queue lists
            $this->db->where_in('email', $chunk);
            $query = $this->db->get(db_prefix() . 'emailcampaign_queue');

            if ($query->num_rows() > 0) {
                $matchedEmails = [];
                foreach ($query->result() as $row) {
                    $matchedEmails[] = $row->email;
                    $deleted[] = $row->email;
                }

                // Delete records
                $this->db->where_in('email', $matchedEmails);
                $this->db->delete(db_prefix() . 'emailcampaign_queue');
            }


            //email survey
            $this->db->where_in('email', $chunk);
            $query = $this->db->get(db_prefix() . 'surveysemailsendcron');

            if ($query->num_rows() > 0) {
                $matchedEmails = [];
                foreach ($query->result() as $row) {
                    $matchedEmails[] = $row->email;
                    $deleted[] = $row->email;
                }

                // Delete records
                $this->db->where_in('email', $matchedEmails);
                $this->db->delete(db_prefix() . 'surveysemailsendcron');
            }


            //email survey list
            $this->db->where_in('email', $chunk);
            $query = $this->db->get(db_prefix() . 'listemails');
            if ($query->num_rows() > 0) {
                $matchedEmails = [];
                foreach ($query->result() as $row) {
                    $matchedEmails[] = $row->email;
                    $deleted[] = $row->email;
                }
                // Delete records
                $this->db->where_in('email', $matchedEmails);
                $this->db->delete(db_prefix() . 'listemails');
            }

            // Identify emails not matched in the current chunk
            $notMatched = array_merge($notMatched, array_diff($chunk, $deleted));
        }

        // Output results
        echo "<br>Emails not matched : " . count($notMatched) . "<br>";
        echo !empty($notMatched) ? implode('<br>', $notMatched) : "All emails matched and updated.<br>";
    }

    public function google_sheet_cron()
    {
        set_time_limit(0);
        $this->load->model('google_sheets_model');
        $this->load->model('leads_model');
        $sheets = $this->google_sheets_model->get_sheets();
        if (empty($sheets)) {
            log_activity('Google Sheets Cron Job: No sheets found');
            return;
        }
        $total_new_records = 0;
        foreach ($sheets as $sheet) {
            $url = $sheet['sheet_url'];
            if (empty($url)) {
                log_activity('Google Sheets Cron Job: Empty URL for sheet ID ' . $sheet['id']);
                continue;
            }
            $existing_records = $this->google_sheets_model->get_sheet_records($sheet['id']);
            $existing_record_ids = [];
            foreach ($existing_records as $record) {
                $existing_record_ids[$record['sheet_record_id']] = $record['id'];
            }
            try {
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                $response = curl_exec($ch);
                if (curl_errno($ch)) {
                    log_activity('Google Sheets Cron Job: Failed to fetch data for sheet ID ' . $sheet['id'] . ': ' . curl_error($ch));
                    curl_close($ch);
                    continue;
                }
                curl_close($ch);
                $lines = explode("\n", $response);
                if (count($lines) <= 1) {
                    log_activity('Google Sheets Cron Job: No data found in sheet ID ' . $sheet['id']);
                    continue;
                }
                $header_row = str_getcsv($lines[0]);
                if (empty($header_row)) {
                    log_activity('Google Sheets Cron Job: Invalid header row in sheet ID ' . $sheet['id']);
                    continue;
                }
                $new_records_count = 0;
                for ($i = 1; $i < count($lines); $i++) {
                    $row = str_getcsv($lines[$i]);
                    if (empty($row) || count($row) != count($header_row)) {
                        continue;
                    }
                    $row_data = [];
                    foreach ($header_row as $index => $column_name) {
                        if (isset($row[$index])) {
                            $row_data[$column_name] = $row[$index];
                        } else {
                            $row_data[$column_name] = '';
                        }
                    }
                    if (!isset($row_data['id']) || empty($row_data['id'])) {
                        continue;
                    }
                    $record_id = $row_data['id'];
                    if (!isset($existing_record_ids[$record_id])) {
                        $sheet_record = [
                            'sheet_id' => $sheet['id'],
                            'sheet_record_id' => $record_id,
                            'record_data' => json_encode($row_data),
                            'lead_id' => null,
                        ];
                        $this->google_sheets_model->add_sheet_record($sheet_record);
                        $new_records_count++;
                    }
                }
                $total_new_records += $new_records_count;
                if ($total_new_records > 0) {
                    log_activity('Google Sheets Cron Job: Added ' . $new_records_count . ' new records for sheet ID ' . $sheet['id']);
                }
            } catch (Exception $e) {
                log_activity('Google Sheets Cron Job: Error processing sheet ID ' . $sheet['id'] . ': ' . $e->getMessage());
            }
        }
    }
    
    // public function leadAssignmentCron()
    // {
    //     $staff_ids = [37, 46, 47, 54, 32, 22, 14, 25, 64, 41, 59, 62, 57, 60, 65, 29];

    //     // Fetch all unassigned leads
    //     $this->db->where('assigned', 0);
    //     $leads = $this->db->get('tblleads')->result();

    //     foreach ($leads as $lead) {
    //         // Pick a random staff id
    //         $random_staff = $staff_ids[array_rand($staff_ids)];

    //         // Update the assigned column
    //         $this->db->where('id', $lead->id); // assuming `id` is the primary key
    //         $this->db->update('tblleads', ['assigned' => $random_staff]);
    //     }
    // }
}
