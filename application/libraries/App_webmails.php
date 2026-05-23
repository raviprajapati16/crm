<?php

defined('BASEPATH') or exit('No direct script access allowed');

@include_once APPPATH . 'vendor/phpmailer/phpmailer/PHPMailerAutoload.php';
class App_webmails
{
    //IMAP
    private $imapHost;
    private $imapPort;
    private $imapUser;
    private $imapPass;
    private $encryption;
    private $imapStream;
    private $mailbox;

    //SMTP
    private $smtpHost;
    private $smtpPort;
    private $smtpUser;
    private $smtpPass;
    private $smtpEncryption;
    private $charSet;

    private $ci;
    private $sentFolder;
    private $trashFolder;
    private $draftFolder;

    public function __construct()
    {
        $this->ci = &get_instance();
        $staff = get_staff(get_staff_user_id());

        if (!isset($staff->webmail_email) || empty($staff->webmail_email)) {
            return [
                'success' => false,
                'message' => 'Webmail email is not set.'
            ];
        }
        if (!isset($staff->webmail_password) || empty($staff->webmail_password)) {
            return [
                'success' => false,
                'message' => 'Webmail password is not set.'
            ];
        }

        // get mail service settings.
        $this->ci->load->model('mailservices_model');
        $mailServiceData = $this->ci->mailservices_model->get($staff->mail_service);

        if (!empty($mailServiceData)) {
            //IMAP
            $this->imapHost = $mailServiceData->imap_host;
            $this->imapPort = $mailServiceData->imap_port;
            $this->imapUser = $staff->webmail_email;
            $this->imapPass =  $staff->webmail_password;
            $this->encryption = $mailServiceData->imap_encryption;
            if (strpos($this->imapHost, 'gmail.com') !== false) {
                $this->mailbox = "{" . $this->imapHost . ":" . $this->imapPort . "/imap/" . $this->encryption . "/novalidate-cert}";
            } else {
                $this->mailbox = "{" . $this->imapHost . ":" . $this->imapPort . "/imap/" . $this->encryption . "}";
            }

            //SMTP
            $this->smtpHost = $mailServiceData->smtp_host;
            $this->smtpPort = $mailServiceData->smtp_port;
            $this->smtpUser = $staff->webmail_email;
            $this->smtpPass = $staff->webmail_password;
            $this->smtpEncryption = $mailServiceData->smtp_encryption;
            $this->charSet = (isset($mailServiceData->email_charset) && !empty($mailServiceData->email_charset)) ? $mailServiceData->email_charset : 'UTF-8';

            if (stripos($this->imapHost, "gmail") !== false) {
                $this->sentFolder = "[Gmail]/Sent Mail";
                $this->trashFolder = "[Gmail]/Trash";
                $this->draftFolder = "[Gmail]/Drafts";
            } else {
                $this->sentFolder = "Sent";
                $this->trashFolder = "Trash";
                $this->draftFolder = "Drafts";
            }
        } else {
            if (!$this->input->is_ajax_request()) {
                set_alert('danger', "Mail service not configured");
                redirect(admin_url());
            }
        }
    }

    private function imap_connect()
    {
        $this->imapStream = @imap_open($this->mailbox, $this->imapUser, $this->imapPass);
    }

    public function authentication()
    {
        $this->imap_connect();
        if ($this->imapStream) {
            $folderList = $this->getFolderList($this->mailbox);
            if ($folderList['success']) {
                $headerInfo = imap_headerinfo($this->imapStream, 1);
                if ($headerInfo) {
                    return [
                        'success' => true,
                        'email' => htmlspecialchars($this->imapUser),
                        'folders' => $folderList['folders']
                    ];
                } else {
                    return [
                        'success' => false,
                        'message' => 'Could not fetch email headers.'
                    ];
                }
            } else {
                return $folderList;
            }
        } else {
            return [
                'success' => false,
                'message' => 'Could not connect to mail server. Error : ' . imap_last_error()
            ];
        }
    }

    public function fetch_emails($data)
    {
        $selectedFolder = $data['folder'];
        $page = isset($data['page']) ? $data['page'] : 1;
        $status = isset($data['status']) ? $data['status'] : null;
        $dates = isset($data['dates']) ? explode(" - ", $data['dates']) : null;
        $pageSize = 15;
        $searchQuery = isset($data['search']) ? $data['search'] : '';

        // Convert DD-MM-YYYY to DD-Month-YYYY for IMAP
        $formatDate = function ($date) {
            return date('d-M-Y', strtotime(str_replace('/', '-', $date)));
        };

        $mailbox = $this->mailbox . $selectedFolder;
        $mailboxStream = imap_open($mailbox, $this->imapUser, $this->imapPass);
        $emails = [];

        if ($mailboxStream) {
            // Build the search query based on status, date range, and search term
            $searchCriteria = '';

            // Handle seen/unseen status
            if (!empty($status)) {
                if ($status === 'seen') {
                    $searchCriteria .= 'SEEN ';
                } elseif ($status === 'unseen') {
                    $searchCriteria .= 'UNSEEN ';
                }
            }

            // Handle search query
            if (!empty($searchQuery)) {
                $searchCriteria .= 'TEXT "' . $searchQuery . '" ';
            }

            // Handle date range filter
            if (!empty($dates) && count($dates) == 2) {
                $fromDate = $formatDate($dates[0]);  // Start date (6 Aug)
                $toDate = $formatDate($dates[1]);    // End date (7 Aug)

                // Add SINCE for the fromDate and UNTIL for the toDate (inclusive)
                $searchCriteria .= 'SINCE "' . $fromDate . '" ';
                $searchCriteria .= 'BEFORE "' . date('d-M-Y', strtotime($toDate . ' +1 day')) . '" ';
            }

            // If no specific search query is provided, use "ALL"
            if (empty($searchCriteria)) {
                $searchCriteria = 'ALL';
            }

            // Perform search with the built criteria
            $searchResults = imap_search($mailboxStream, trim($searchCriteria));

            // Sort the search results by date in descending order (newest first)
            if ($searchResults) {
                $searchResults = imap_sort($mailboxStream, SORTDATE, 1, SE_UID, trim($searchCriteria));
            }

            $totalEmails = 0;
            if ($searchResults) {
                $totalEmails = count($searchResults);
                $startIndex = ($page - 1) * $pageSize;
                $endIndex = min($startIndex + $pageSize, $totalEmails);

                for ($i = $startIndex; $i < $endIndex; $i++) {
                    $messageNumber = $searchResults[$i];
                    $header = imap_headerinfo($mailboxStream, imap_msgno($mailboxStream, $messageNumber));

                    $isSentOrDraft = in_array($selectedFolder, [$this->sentFolder, $this->draftFolder]);
                    if ($isSentOrDraft) {
                        $recipient = isset($header->to[0]) ? $header->to[0] : null;
                        $fromName = isset($recipient->personal) && !empty($recipient->personal) ? $recipient->personal : (isset($recipient->mailbox) ? $recipient->mailbox : "(No Recipient)");
                        $fromEmail = isset($recipient->mailbox) && isset($recipient->host) ? $recipient->mailbox . '@' . $recipient->host : "";
                    } else {
                        $from = $header->from[0];
                        $fromName = isset($from->personal) && !empty($from->personal) ? $from->personal : (isset($from->mailbox) ? $from->mailbox : "(No Recipient)");
                        $fromEmail = isset($from->mailbox) && isset($from->host) ? $from->mailbox . '@' . $from->host : "";
                    }

                    if ($fromName == "undisclosed-recipients") {
                        $fromName = "No Recipients";
                        $fromEmail = "";
                    }

                    $emails[] = [
                        'subject' => isset($header->subject) ? $this->decodeSubject($header->subject) : "(No Subject)",
                        'from_name' => $fromName,
                        'from_email' => $fromEmail,
                        'date' => isset($header->date) ? $header->date : $header->MailDate,
                        'is_read' => ($header->Unseen == "U") ? 0 : 1,
                        'mail_no' => isset($header->Msgno) ? $header->Msgno : "",
                    ];
                }
            }

            imap_close($mailboxStream);
            return [
                'success' => true,
                'emails' => $emails,
                'total_pages' => ceil($totalEmails / $pageSize),
                'current_page' => $page,
                'total_emails' => $totalEmails,
                'record_start_at' => ($pageSize * ($page - 1)) + 1,
                'record_end_at' => min($totalEmails, $pageSize * $page),
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Could not connect.' . imap_last_error()
            ];
        }
    }


    public function delete_emails($data)
    {
        $this->imap_connect();
        if ($this->imapStream) {
            $folder = $data['folder'];
            $imapStream = imap_open($this->mailbox . $folder, $this->imapUser, $this->imapPass);
            $success = true;
            $errorMessages = [];
            foreach ($data['email_no'] as $email_number) {
                $header = imap_headerinfo($imapStream, $email_number);
                if ($folder == $this->draftFolder || $folder == $this->trashFolder) {
                    if (!imap_delete($imapStream, $email_number)) {
                        $success = false;
                        $errorMessages[] = $folder . ' email number ' . $email_number . ' not deleted. Error: ' . imap_last_error();
                    }
                } else {
                    if (!imap_mail_move($imapStream, $email_number, $this->trashFolder)) {
                        $success = false;
                        $errorMessages[] = 'Email number ' . $email_number . ' not moved to Trash. Error: ' . imap_last_error();
                    }
                }
            }

            if ($success) {
                imap_expunge($imapStream);
                imap_close($imapStream);
                return [
                    'success' => true,
                    'message' => 'Email(s) successfully deleted.'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Some emails were not deleted. Errors: ' . implode(' ', $errorMessages)
                ];
            }
        } else {
            return [
                'success' => false,
                'message' => 'Could not connect. ' . imap_last_error()
            ];
        }
    }


    public function move_to_inbox($data)
    {
        $imapStream = imap_open($this->mailbox . $data['folder'], $this->imapUser, $this->imapPass);
        if ($imapStream) {
            $success = true;
            $errorMessages = [];
            foreach ($data['email_no'] as $email_number) {
                // Move emails from Trash to INBOX
                if (!imap_mail_move($imapStream, $email_number, 'INBOX')) {
                    $success = false;
                    $errorMessages[] = 'Email number ' . $email_number . ' not moved to INBOX. Error: ' . imap_last_error();
                }
            }
            if ($success) {
                if (!imap_expunge($imapStream)) {
                    $success = false;
                    $errorMessages[] = 'Error expunging messages. ' . imap_last_error();
                }
                imap_close($imapStream);
                return [
                    'success' => true,
                    'message' => 'Email(s) successfully moved to INBOX.'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Some emails were not processed. Errors: ' . implode(' ', $errorMessages)
                ];
            }
        } else {
            return [
                'success' => false,
                'message' => 'Could not connect. ' . imap_last_error()
            ];
        }
    }

    public function mark_as_read($data)
    {
        $this->imap_connect();
        if ($this->imapStream) {
            $success = true;
            $errorMessages = [];
            $type = '\\Seen';

            if (!empty($data['folder'])) {
                $folder = $data['folder'];
                if (!imap_reopen($this->imapStream, $this->mailbox . $folder)) {
                    return [
                        'success' => false,
                        'message' => 'Could not select folder: ' . $folder . '. Error: ' . imap_last_error()
                    ];
                }
            }

            foreach ($data['email_no'] as $email_number) {
                if ($data['type'] == "read") {
                    if (!imap_setflag_full($this->imapStream, $email_number, $type)) {
                        $success = false;
                        $errorMessages[] = 'Email number ' . $email_number . ' not marked as read. Error: ' . imap_last_error();
                    }
                } else {
                    if (!imap_clearflag_full($this->imapStream, $email_number, $type)) {
                        $success = false;
                        $errorMessages[] = 'Email number ' . $email_number . ' not marked as unread. Error: ' . imap_last_error();
                    }
                }
            }

            if ($success) {
                return [
                    'success' => true,
                    'message' => 'Email(s) successfully marked as ' . $data['type'] . '.'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Email(s) were not marked as ' . $data['type'] . '. Errors: ' . implode(' ', $errorMessages)
                ];
            }
        } else {
            return [
                'success' => false,
                'message' => 'Could not connect. ' . imap_last_error()
            ];
        }
    }


    private function decodeSubject($subject)
    {
        $decodedSubject = '';
        $elements = imap_mime_header_decode($subject);

        foreach ($elements as $element) {
            if ($element->charset == 'default') {
                $decodedSubject .= $element->text;
            } else {
                $decodedText = imap_mime_header_decode($element->text);
                if (is_array($decodedText)) {
                    $decodedSubject .= implode('', array_column($decodedText, 'text'));
                } else {
                    $decodedSubject .= $decodedText;
                }
            }
        }

        return $decodedSubject;
    }

    public function send_email($data)
    {
        $to = isset($data['to']) ? $data['to'] : [];
        $subject = isset($data['subject']) ? $data['subject'] : '';
        $body = isset($data['body']) ? $data['body'] : '';
        $cc = isset($data['cc']) ? $data['cc'] : [];
        $bcc = isset($data['bcc']) ? $data['bcc'] : [];
        $replyTo = isset($data['replyto']) ? $data['replyto'] : [];
        $attachments = isset($data['uploaded_files']) ? $data['uploaded_files'] : [];

        $mail = new PHPMailer(true);
        $name = get_option('brandname');
        $staffData = get_staff(get_staff_user_id());
        if (!empty($staffData)) {
            $name = $staffData->firstname . " " . $staffData->lastname;
        }

        try {
            $mail->isSMTP();
            $mail->Host = $this->smtpHost;
            $mail->SMTPAuth = true;
            $mail->Username = $this->smtpUser;
            $mail->Password = $this->smtpPass;
            $mail->SMTPSecure = $this->smtpEncryption;
            $mail->Port = $this->smtpPort;
            $mail->CharSet = $this->charSet;
            $mail->setFrom($this->smtpUser, $name);

            if (!empty($to)) {
                if (is_array($to)) {
                    foreach ($to as $recipient) {
                        $mail->addAddress($recipient);
                    }
                } else {
                    $mail->addAddress($to);
                }
            }

            if (!empty($cc)) {
                if (is_array($cc)) {
                    foreach ($cc as $ccRecipient) {
                        $mail->addCC($ccRecipient);
                    }
                } else {
                    $mail->addCC($cc);
                }
            }
            if (IS_LIVE && get_global_emails('cc')) {
                $ccGlobal = get_global_emails('cc');
                if (!empty($ccGlobal)) {
                    foreach ($ccGlobal as $ccGlobalRecipient) {
                        $mail->addCC($ccGlobalRecipient);
                    }
                }
            }

            if (!empty($bcc)) {
                if (is_array($bcc)) {
                    foreach ($bcc as $bccRecipient) {
                        $mail->addBCC($bccRecipient);
                    }
                } else {
                    $mail->addBCC($bcc);
                }
            }

            if (IS_LIVE && get_global_emails('bcc')) {
                $bccGlobal = get_global_emails('bcc');
                if (!empty($bccGlobal)) {
                    foreach ($bccGlobal as $bccGlobalRecipient) {
                        $mail->addBCC($bccGlobalRecipient);
                        $mail->addReplyTo($bccGlobalRecipient);
                    }
                }
            }

            //Reply to
            $mail->addReplyTo($this->smtpUser);
            if (!empty($replyTo)) {
                if (is_array($replyTo)) {
                    foreach ($replyTo as $replyRecipient) {
                        $mail->addReplyTo($replyRecipient);
                    }
                } else {
                    $mail->addReplyTo($replyTo);
                }
            }

            if (!empty($attachments)) {
                foreach ($attachments as $attachment) {
                    $mail->addAttachment($attachment);
                }
            }

            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $body;
            $mail->addCustomHeader('List-Unsubscribe', "<mailto:$this->smtpUser>");

            $mail->send();
            $this->save_to_sent_folder($mail);
            if (isset($data['draft_no'])) {
                $this->deleteDraft($data['draft_no']);
            }
            return [
                'success' => true,
                'message' => 'Email has been sent'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Email could not be sent. Mailer Error: ' . $mail->ErrorInfo
            ];
        }
    }

    public function saveDraftEmail($params)
    {
        $this->imap_connect();
        if (!$this->imapStream) {
            return [
                'success' => false,
                'message' => 'Could not connect. ' . imap_last_error()
            ];
        }

        $username = $this->imapUser;
        $to = $this->formatEmailList($params['to']);
        $subject = $params['subject'];
        $body = $params['body'];
        $attachments = isset($params['uploaded_files']) ? $params['uploaded_files'] : [];
        $cc = $this->formatEmailList($params['cc'] ?? []);
        $bcc = $this->formatEmailList($params['bcc'] ?? []);
        $replyTo = $this->formatEmailList($params['replyto'] ?? []);

        $headers = $this->buildHeaders($username, $to, $subject, $cc, $bcc, $replyTo);
        $message = $this->buildMessage($body, $attachments);

        $check = imap_append($this->imapStream, $this->mailbox . $this->draftFolder, $headers . "\r\n" . $message, "\\Seen");

        if ($check) {
            $deleteDraftResult = $this->handleDraftDeletion($params);
            if (!$deleteDraftResult['success']) {
                return $deleteDraftResult;
            }

            return [
                'success' => true,
                'message' => 'Draft saved successfully'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Could not save draft. ' . imap_last_error()
            ];
        }
    }

    private function formatEmailList($emails)
    {
        if (is_array($emails)) {
            return implode(', ', $emails);
        }

        return $emails;
    }

    private function buildHeaders($username, $to, $subject, $cc, $bcc, $replyTo)
    {
        $headers = "From: $username\r\n";
        $headers .= "To: $to\r\n";
        $headers .= "Subject: $subject\r\n";
        if (!empty($cc)) {
            $headers .= "Cc: $cc\r\n";
        }
        if (!empty($bcc)) {
            $headers .= "Bcc: $bcc\r\n";
        }
        if (!empty($replyTo)) {
            $headers .= "Reply-To: $replyTo\r\n";
        }
        $headers .= "MIME-Version: 1.0\r\n";
        $boundary = "PHP-mixed-" . md5(time());
        $headers .= "Content-Type: multipart/mixed; boundary=\"$boundary\"\r\n";

        return $headers;
    }

    private function buildMessage($body, $attachments)
    {
        $boundary = "PHP-mixed-" . md5(time());
        $message = "--$boundary\r\n";
        $message .= "Content-Type: text/html; charset=\"utf-8\"\r\n";
        $message .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
        $message .= $body . "\r\n\r\n";

        if (!empty($attachments)) {
            $message .= $this->buildAttachmentParts($boundary, $attachments);
        }

        $message .= "--$boundary--\r\n";

        return $message;
    }

    private function buildAttachmentParts($boundary, $attachments)
    {
        $attachmentParts = '';
        foreach ($attachments as $file) {
            if (file_exists($file) && is_readable($file)) {
                $fileName = basename($file);
                $fileData = file_get_contents($file);
                $fileType = mime_content_type($file);

                $attachmentParts .= "--$boundary\r\n";
                $attachmentParts .= "Content-Type: $fileType; name=\"$fileName\"\r\n";
                $attachmentParts .= "Content-Transfer-Encoding: base64\r\n";
                $attachmentParts .= "Content-Disposition: attachment; filename=\"$fileName\"\r\n\r\n";

                // Chunk-split base64 encoded file data
                $fileData = base64_encode($fileData);
                $fileData = chunk_split($fileData);

                $attachmentParts .= $fileData . "\r\n";
            }
        }

        return $attachmentParts;
    }


    private function handleDraftDeletion($params)
    {
        if (isset($params['draft_no'])) {
            $deleteDraft = $this->deleteDraft($params['draft_no']);
            if (!$deleteDraft['success']) {
                return $deleteDraft;
            }
        }

        return [
            'success' => true
        ];
    }

    private function deleteDraft($msgno)
    {
        $imapStream = imap_open($this->mailbox . $this->draftFolder, $this->imapUser, $this->imapPass);
        if (!$imapStream) {
            return [
                'success' => false,
                'message' => 'Cannot select folder. ' . imap_last_error()
            ];
        }
        if (!imap_delete($imapStream, $msgno)) {
            return [
                'success' => false,
                'message' => 'Cannot delete email. ' . imap_last_error()
            ];
        }
        if (!imap_expunge($imapStream)) {
            return [
                'success' => false,
                'message' => 'Cannot expunge mailbox. ' . imap_last_error()
            ];
        }
        return [
            'success' => true,
        ];
    }


    private function save_to_sent_folder($mail)
    {
        $this->imap_connect();
        if ($this->imapStream) {
            $message = $mail->getSentMIMEMessage();
            $result = imap_append($this->imapStream, $this->mailbox . $this->sentFolder, $message, "\\Seen");
            if ($result) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    function decodeEmlAttachments($emlContent)
    {
        $parts = explode("--b1_", $emlContent);
        foreach ($parts as $part) {
            if (strpos($part, "Content-Type: message/rfc822") !== false) {
                preg_match('/filename="(.*?)"/', $part, $matches);
                $fileName = isset($matches[1]) ? $matches[1] : "unknown";
                if (strpos($fileName, '.eml') !== false) {
                    $base64encode = substr($part, strpos($part, "\r\n\r\n") + 4);
                    $contentParts = preg_split('/\R\R/', $base64encode, 2);
                    $encodeContent = isset($contentParts[1]) ? trim($contentParts[1]) : '';
                    if (!empty($encodeContent)) {
                        $base64decodeContent = base64_decode($encodeContent);
                        $emlContent = str_replace($encodeContent, $base64decodeContent, $emlContent);
                    }
                }
            }
        }
        return $emlContent;
    }

    public function fetch_single_email($data, $with_attachments = true)
    {
        $folder = $data['folder'];
        $msgNumber = $data['email_no'];
        $this->imapStream = imap_open($this->mailbox . $folder, $this->imapUser, $this->imapPass);
        if ($this->imapStream) {

            $header = imap_fetchheader($this->imapStream, $msgNumber);
            if ($header === false) {
                imap_close($this->imapStream);
                return [
                    'success' => false,
                    'message' => 'Error fetching header: ' . imap_last_error()
                ];
            }

            $structure = imap_fetchstructure($this->imapStream, $msgNumber);
            if ($structure === false) {
                imap_close($this->imapStream);
                return [
                    'success' => false,
                    'message' => 'Error fetching structure: ' . imap_last_error()
                ];
            }

            $body = $this->getEmailBody($this->imapStream, $msgNumber, $structure);

            $attachments = [];
            if ($with_attachments) {
                if (isset($structure->parts) && count($structure->parts)) {
                    for ($i = 0; $i < count($structure->parts); $i++) {
                        $attachments[$i] = [
                            'is_attachment' => false,
                            'filename' => '',
                            'name' => '',
                            'attachment' => ''
                        ];

                        if ($structure->parts[$i]->ifdparameters) {
                            foreach ($structure->parts[$i]->dparameters as $object) {
                                if (strtolower($object->attribute) == 'filename') {
                                    $attachments[$i]['is_attachment'] = true;
                                    $attachments[$i]['filename'] = $object->value;
                                }
                            }
                        }

                        if ($structure->parts[$i]->ifparameters) {
                            foreach ($structure->parts[$i]->parameters as $object) {
                                if (strtolower($object->attribute) == 'name') {
                                    $attachments[$i]['is_attachment'] = true;
                                    $attachments[$i]['name'] = $object->value;
                                }
                            }
                        }

                        if ($attachments[$i]['is_attachment']) {
                            $attachments[$i]['attachment'] = imap_fetchbody($this->imapStream, $msgNumber, $i + 1);
                            switch ($structure->parts[$i]->encoding) {
                                case 3:
                                    $attachments[$i]['attachment'] = base64_decode($attachments[$i]['attachment']);
                                    break;
                                case 4:
                                    $attachments[$i]['attachment'] = quoted_printable_decode($attachments[$i]['attachment']);
                                    break;
                            }
                            $attachments[$i] = $this->download_attachment($attachments[$i]);
                        }
                    }
                }
            }

            $headerInfo = imap_headerinfo($this->imapStream, $msgNumber);
            $from = $headerInfo->from[0];
            $fromName = isset($from->personal) && !empty($from->personal) ? $from->personal : (isset($from->mailbox) ? $from->mailbox : "");
            $fromEmail = isset($from->mailbox) && isset($from->host) ? $from->mailbox . '@' . $from->host : "";

            return [
                'success' => true,
                'email' => [
                    'subject' => isset($headerInfo->subject) ? $this->decodeSubject($headerInfo->subject) : "(No Subject)",
                    'from_name' => $fromName,
                    'from_email' => $fromEmail,
                    'date' => isset($headerInfo->date) ? $headerInfo->date : $headerInfo->MailDate,
                    'is_read' => ($headerInfo->Unseen == "U") ? 0 : 1,
                    'mail_no' => isset($headerInfo->Msgno) ? $headerInfo->Msgno : "",
                    'header' => $headerInfo,
                    'body' => $body,
                    'attachments' => $attachments
                ]
            ];
            imap_close($this->imapStream);
        } else {
            return [
                'success' => false,
                'message' => 'Could not connect to mail server. Error : ' . imap_last_error()
            ];
        }
    }

    public function create_eml_files($data)
    {
        $attachments = [];
        $folder = $data['folder'];
        $this->imapStream = imap_open($this->mailbox . $folder, $this->imapUser, $this->imapPass);

        $subject_text = "";
        if ($this->imapStream) {
            foreach ($data['email_no'] as $key => $msgNumber) {
                $headerInfo = imap_headerinfo($this->imapStream, $msgNumber);
                $subject = $headerInfo->subject ?: 'no_subject';
                $filename = preg_replace('/[^A-Za-z0-9 _.-]/', '_', $subject) . '.eml';
                if ($key == 0) {
                    $subject_text = $subject;
                }
                $temp_folder = time() . uniqid();
                $downloadPath = "uploads/temp_mail_attachments/$temp_folder";
                _maybe_create_upload_path($downloadPath);

                $filePath = $downloadPath . DIRECTORY_SEPARATOR . $filename;

                if (imap_savebody($this->imapStream, $filePath, $msgNumber, "", FT_PEEK)) {
                    $attachments[] = [
                        "is_attachment" => 1,
                        "filename" => $filename,
                        "attachment" => $filePath
                    ];
                }
            }

            imap_close($this->imapStream);
        }

        $result['success'] = true;
        $result['email']['subject'] = "Fwd : " . $subject_text;
        $result['email']['attachments'] = $attachments;
        return $result;
    }

    public function download_attachment($attachment)
    {
        $temp_folder = time() . uniqid();
        $downloadPath = "uploads/temp_mail_attachments/$temp_folder";
        if ($attachment['is_attachment']) {
            $filename = $attachment['filename'] ?: $attachment['name'];
            if ($filename) {
                $filePath = $downloadPath . DIRECTORY_SEPARATOR;
                _maybe_create_upload_path($filePath);
                $filename = unique_filename($filePath, $filename);
                $filePath = $filePath . $filename;
                file_put_contents($filePath, $attachment['attachment']);
                $attachment['filename'] = $filename;
                $attachment['attachment'] = $filePath;
            }
        }
        return $attachment;
    }

    private function getEmailBody($mailboxStream, $messageNumber, $structure, $partNumberPrefix = '')
    {
        $body = '';
        $htmlBody = '';

        if ($structure->type == 1 && isset($structure->parts) && count($structure->parts)) {
            foreach ($structure->parts as $partNumber => $part) {
                $currentPartNumber = $partNumberPrefix ? "$partNumberPrefix.$partNumber" : ($partNumber + 1);

                if ($part->type == 0) {
                    $decodedPart = $this->decodePart($mailboxStream, $messageNumber, $part, $currentPartNumber);
                    if ($part->subtype == 'HTML') {
                        $htmlBody = $decodedPart;
                    } elseif ($part->subtype == 'PLAIN' && empty($body)) {
                        $body = $decodedPart;
                    }
                } elseif ($part->type == 1) {
                    $nestedBody = $this->getEmailBody($mailboxStream, $messageNumber, $part, $currentPartNumber);
                    if ($nestedBody) {
                        return $nestedBody;
                    }
                }
            }
        } else {
            if ($structure->subtype == 'HTML' || $structure->subtype == 'PLAIN') {
                return $this->decodePart($mailboxStream, $messageNumber, $structure, 1);
            }
        }

        return !empty($htmlBody) ? $htmlBody : $body;
    }

    private function decodePart($mailboxStream, $messageNumber, $part, $partNumber)
    {
        $data = imap_fetchbody($mailboxStream, $messageNumber, $partNumber);

        switch ($part->encoding) {
            case 3:
                return imap_base64($data);
            case 4:
                return quoted_printable_decode($data);
            case 1:
                return imap_8bit($data);
            case 2:
                return imap_binary($data);
            case 0:
            case 5:
            default:
                return $data;
        }
    }

    private function getFolderList()
    {
        $folders = imap_list($this->imapStream, $this->mailbox, '*');
        $folderList = $this->folderSequnce($folders);
        if (!empty($folderList)) {
            $folderData = [];
            foreach ($folderList as $folder) {
                $status = imap_status($this->imapStream, $folder, SA_UNSEEN);
                $unreadCount = $status ? $status->unseen : 0;
                $folderData[] = [
                    'name' =>  preg_replace('/{.*}/', '', $folder),
                    'unreadCount' => $unreadCount
                ];
            }

            return [
                'success' => true,
                'folders' => $folderData
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Could not fetch folder list.'
            ];
        }
    }

    private function folderSequnce($mailboxes)
    {
        $priorityFolders = [];
        $otherFolders = [];

        $priorityOrder = [
            'INBOX',
            'Sent',
            'Drafts',
            'Trash',
            'Spam',
            'All Mail',
            'Starred',
            'Important'
        ];

        foreach ($mailboxes as $mailbox) {

            if (strpos($mailbox, 'INBOX/') !== false) {
                $otherFolders[] = $mailbox;
                continue;
            }

            $matched = false;
            foreach ($priorityOrder as $priority) {
                if (stripos($mailbox, $priority) !== false) {
                    $priorityFolders[$priority] = $mailbox;
                    $matched = true;
                    break;
                }
            }
            if (!$matched) {
                $otherFolders[] = $mailbox;
            }
        }

        $sortedMailboxes = [];
        foreach ($priorityOrder as $priority) {
            if (isset($priorityFolders[$priority])) {
                $sortedMailboxes[] = $priorityFolders[$priority];
            }
        }
        return array_merge($sortedMailboxes, $otherFolders);
    }

    public function getInboxUnreadCount()
    {
        $this->imap_connect();
        if ($this->imapStream) {
            $folderName = 'INBOX';
            $status = imap_status($this->imapStream, $this->mailbox . $folderName, SA_UNSEEN);
            $unreadCount = $status ? $status->unseen : 0;
            return $unreadCount;
        } else {
            return '0';
        }
    }

    public function webmail_folder_save($data)
    {
        $this->imap_connect();
        if ($this->imapStream) {
            $folderSlug = "INBOX/";
            if (stripos($this->imapHost, "gmail") !== false) {
                $folderSlug = "";
            }
            $new_folder = $folderSlug . $data['foldername'];
            $encoded_new_folder = imap_utf7_encode($this->mailbox . $new_folder);
            $folders = imap_list($this->imapStream, $this->mailbox, '*');

            if (isset($data['oldFolderName']) && !empty($data['oldFolderName'])) {
                $old_folder = isset($data['oldFolderName']) ? $folderSlug . $data['oldFolderName'] : null;
                $encoded_old_folder = $old_folder ? imap_utf7_encode($this->mailbox . $old_folder) : null;
                if ($encoded_old_folder && $folders && in_array($encoded_old_folder, $folders)) {
                    if (!empty($data['foldername'])) {
                        if (imap_renamemailbox($this->imapStream, $encoded_old_folder, $encoded_new_folder)) {
                            imap_subscribe($this->imapStream, $encoded_new_folder);
                            return [
                                'success' => true,
                                'message' => "Folder successfully renamed from " . $data['oldFolderName'] . " to " . $data['foldername'] . ".",
                            ];
                        } else {
                            return [
                                'success' => false,
                                'message' => "Failed to rename the folder."
                            ];
                        }
                    } else {
                        return [
                            'success' => false,
                            'message' => "New folder name not provided."
                        ];
                    }
                }
            }

            // Create a new folder if it doesn't already exist
            if ($folders && !in_array($encoded_new_folder, $folders)) {
                if (imap_createmailbox($this->imapStream, $encoded_new_folder)) {
                    imap_subscribe($this->imapStream, $encoded_new_folder);
                    return [
                        'success' => true,
                        'message' => "Folder successfully created.",
                    ];
                } else {
                    return [
                        'success' => false,
                        'message' => "Failed to create the folder."
                    ];
                }
            }

            // Folder already exists and no rename operation is needed
            return [
                'success' => false,
                'message' => "Folder already exists and no rename operation is needed."
            ];
        }

        return [
            'success' => false,
            'message' => "Unable to connect to the mail server."
        ];
    }

    public function webmail_move_to_folder($data)
    {
        $this->imap_connect();
        if ($this->imapStream) {
            $imapStream = imap_open($this->mailbox . $data['old_folder'], $this->imapUser, $this->imapPass);
            $success = true;
            $errorMessages = [];
            if (!empty($data['emails'])) {
                foreach ($data['emails'] as $email_number) {
                    if (!imap_mail_move($imapStream, $email_number, $data['new_folder'])) {
                        $success = false;
                        $errorMessages[] = 'Email number ' . $email_number . ' not moved to Trash. Error: ' . imap_last_error();
                    }
                }
            }
            if ($success) {
                imap_expunge($imapStream);
                imap_close($imapStream);
                return [
                    'success' => true,
                    'message' => 'Email(s) successfully moved.'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Some emails were not moved. Errors: ' . implode(' ', $errorMessages)
                ];
            }
        } else {
            return [
                'success' => false,
                'message' => 'Could not connect. ' . imap_last_error()
            ];
        }
    }

    public function webmail_delete_folder($data)
    {
        $this->imap_connect();
        if ($this->imapStream) {
            $folderName = $data['folder'];
            if (empty($folderName)) {
                return [
                    'success' => false,
                    'message' => 'Folder name is required.'
                ];
            }
            $mailboxPath = $this->mailbox . $folderName;
            if (imap_deletemailbox($this->imapStream, $mailboxPath)) {
                return [
                    'success' => true,
                    'message' => 'Folder deleted successfully.'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Could not delete folder. ' . imap_last_error()
                ];
            }
        } else {
            return [
                'success' => false,
                'message' => 'Could not connect. ' . imap_last_error()
            ];
        }
    }



    public function __destruct()
    {
        if ($this->imapStream) {
            imap_close($this->imapStream);
        }
    }
}
