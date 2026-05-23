<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Email_track extends App_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function update($type, $hash)
    {
        date_default_timezone_set('Asia/Kolkata');
        $this->load->database();
        $updateData = [
            'email_open_at' => date('Y-m-d H:i:s')
        ];
        $this->db->where('hash', $hash);
        $this->db->update(db_prefix() . 'emailcampaign_queue', $updateData);
    }

    public function testEmailSend()
    {
        $post_data = $this->input->post();
        if (isset($post_data['email']) && !empty($post_data['email']) && isset($post_data['template_id']) && !empty($post_data['template_id'])) {
            $template =  $this->db->select('*')
                ->from(db_prefix() . 'emailcampaign_templates')
                ->where('id', $post_data['template_id'])
                ->get()->row();
            if (!empty($template)) {
                $subject = $template->subject;
                $template_file = FCPATH . 'uploads/email_campaign_templates/' . $template->id . "/index.html";
                if (file_exists($template_file)) {
                    $mailBody = file_get_contents($template_file);
                    $pattern = "/\*\|.*?\|\*/";
                    $mailBody = preg_replace($pattern, '', $mailBody);
                    $mailArr['from'] = get_option('brandname');
                    $mailArr['email'] =  get_option('smtp_email');
                    $mailArr['password'] = get_instance()->encryption->decrypt(get_option('smtp_password'));
                    $mailArr['smtp_host'] = get_option('smtp_host');
                    $mailArr['smtp_encryption'] = get_option('smtp_encryption');
                    $mailArr['smtp_port'] = get_option('smtp_port');
                    $mailArr['charset'] = get_option('smtp_email_charset');
                    $mailArr['to'] = $post_data['email'];
                    $mailArr['subject'] = $subject;
                    $mailArr['email_body'] = $mailBody;
                    $this->load->library('app_email_campaign');
                    $emailStatus = $this->app_email_campaign->send_email($mailArr);
                    if (isset($emailStatus['success']) && isset($emailStatus['message'])) {
                        $result['success'] = $emailStatus['success'];
                        $result['message'] = $emailStatus['message'];
                    } else {
                        $result['success'] = false;
                        $result['message'] = "Error : Email is not send.";
                    }
                } else {
                    $result['success'] = false;
                    $result['message'] = "Template file not exists on server.";
                }
            } else {
                $result['success'] = false;
                $result['message'] = "Template records not exists.";
            }
        } else {
            $result['success'] = false;
            $result['message'] = "Invalid request params.";
        }
        echo json_encode($result);
    }
}
