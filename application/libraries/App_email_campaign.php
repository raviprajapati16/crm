<?php

defined('BASEPATH') or exit('No direct script access allowed');

@include_once APPPATH . 'vendor/phpmailer/phpmailer/PHPMailerAutoload.php';
class App_email_campaign
{

    public function __construct()
    {
        $this->ci = &get_instance();
    }


    public function send_email($data)
    {
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = $data['smtp_host'];
            $mail->SMTPAuth = true;
            $mail->Username = $data['email'];
            $mail->Password = $data['password'];
            $mail->SMTPSecure = $data['smtp_encryption'];
            $mail->Port = $data['smtp_port'];
            $mail->setFrom($data['email'], $data['from']);
            $mail->addAddress($data['to']);
            $mail->isHTML(true);
            $mail->Subject = $data['subject'];
            $mail->Body = $data['email_body'];
            $mail->CharSet = (isset($data['charset']) && !empty($data['charset'])) ? $data['charset'] : 'UTF-8';
            $replyTo = isset($data['reply_to']) ? $data['reply_to'] : [];
            if (!empty($replyTo)) {
                if (is_array($replyTo)) {
                    foreach ($replyTo as $replyRecipient) {
                        $mail->addReplyTo($replyRecipient);
                    }
                } else {
                    $mail->addReplyTo($replyTo);
                }
            }
            if ($mail->send()) {
                return [
                    'success' => 'sent',
                    'message' => 'Email has been sent successfully'
                ];
            } else {
                return [
                    'success' => 'failed',
                    'message' => 'Email could not be sent. Please try again later.'
                ];
            }
        } catch (Exception $e) {
            switch ($e->getCode()) {
                case 0:
                    return [
                        'success' => 'failed',
                        'message' => 'Mailer Error: ' . $e->getMessage()
                    ];
                case 1:
                    return [
                        'success' => 'failed',
                        'message' => 'SMTP Host could not be found. Please check the host address.'
                    ];
                case 2:
                    return [
                        'success' => 'failed',
                        'message' => 'SMTP Authentication failed. Please check your username and password.'
                    ];
                default:
                    return [
                        'success' => 'failed',
                        'message' => 'Email could not be sent due to an unknown error: ' . $e->getMessage()
                    ];
            }
        }
    }
}
