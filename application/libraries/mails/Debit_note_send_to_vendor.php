<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Debit_note_send_to_vendor extends App_mail_template
{
    protected $for = 'customer';

    protected $debit_note;

    protected $vendor;

    public $slug = 'debit-note-send-to-vendor';

    public $rel_type = 'debit_note';

    public function __construct($debit_note, $vendor, $cc = '', $reply_to = '')
    {
        parent::__construct();

        $this->debit_note = $debit_note;
        $this->vendor = $vendor;
        $this->cc = $cc;
        $this->reply_to = $reply_to;
    }

    public function build()
    {
        if ($this->ci->input->post('email_attachments')) {
            $_other_attachments = $this->ci->input->post('email_attachments');
            foreach ($_other_attachments as $attachment) {
                $_attachment = $this->ci->misc_model->get_file($attachment);
                $this->add_attachment([
                    'attachment' => get_upload_path_by_type('debit_note') . $this->debit_note->id . '/' . $_attachment->file_name,
                    'filename' => $_attachment->file_name,
                    'type' => $_attachment->filetype,
                    'read' => true,
                ]);
            }
        }

        $this->to($this->vendor->email)
            ->set_rel_id($this->debit_note->id)
            ->set_merge_fields('vendors_merge_fields', $this->debit_note->vendorid)
            ->set_merge_fields('debit_note_merge_fields', $this->debit_note->id);
    }
}
